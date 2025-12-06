<?php

namespace App\Services;

use App\Enums\OutgoingTransactionStatus;
use App\Models\Customer;
use App\Models\OutgoingTransaction;
use App\Models\OutgoingTransactionItem;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OutgoingTransactionService extends BaseTransactionService
{
    public function indexQuery(array $filters = [], ?User $user = null): Builder
    {
        $filters = $this->normalizeIndexFilters($filters);

        $query = OutgoingTransaction::query()
            ->with(['createdBy', 'approvedBy']);

        if ($filters['search'] !== '') {
            $query->where(function (Builder $searchQuery) use ($filters): void {
                $searchQuery
                    ->where('transaction_number', 'like', '%'.$filters['search'].'%')
                    ->orWhere('customer_name', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['status'])) {
            $query->whereIn('status', array_map(static fn (OutgoingTransactionStatus $status) => $status->value, $filters['status']));
        }

        if (! empty($filters['customer_ids'])) {
            $customerNames = Customer::whereIn('id', $filters['customer_ids'])
                ->pluck('name')
                ->filter()
                ->values();

            if ($customerNames->isNotEmpty()) {
                $query->whereIn('customer_name', $customerNames);
            }
        }

        if ($filters['date_from']) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        $this->applyStaffScope($query, $user);

        return $query
            ->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');
    }

    public function create(array $validatedData, User $creator): OutgoingTransaction
    {
        $itemsData = $this->extractItems($validatedData);

        return $this->runWithNumberRetry(function () use ($validatedData, $creator, $itemsData): OutgoingTransaction {
            return DB::transaction(function () use ($validatedData, $creator, $itemsData): OutgoingTransaction {
                $transactionNumber = $this->numberGenerator->generateDailySequence(
                    (new OutgoingTransaction)->getTable(),
                    'transaction_number',
                    'SO'
                );

                $transaction = OutgoingTransaction::create([
                    'transaction_number' => $transactionNumber,
                    'transaction_date' => $validatedData['transaction_date'],
                    'customer_name' => $validatedData['customer_name'],
                    'created_by' => $creator->id,
                    'approved_by' => null,
                    'status' => OutgoingTransaction::STATUS_PENDING,
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'total_amount' => 0,
                    'notes' => $validatedData['notes'] ?? null,
                ]);

                $this->createItems($transaction, $itemsData);

                $transaction->load('items');
                $transaction->recalculateTotals();

                $this->logActivity(
                    $creator,
                    'CREATE_OUTGOING',
                    $this->formatDescription($creator, 'CREATE', 'OutgoingTransaction #'.$transaction->transaction_number),
                    $transaction
                );

                return $transaction;
            });
        });
    }

    public function update(OutgoingTransaction $transaction, array $validatedData, User $updater): OutgoingTransaction
    {
        if (! $transaction->isPending()) {
            throw new DomainException('Only pending transactions can be updated.');
        }

        $itemsData = $this->extractItems($validatedData);

        return DB::transaction(function () use ($transaction, $validatedData, $itemsData, $updater) {
            $transaction->update([
                'transaction_date' => $validatedData['transaction_date'],
                'customer_name' => $validatedData['customer_name'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            $transaction->items()->delete();

            $this->createItems($transaction, $itemsData);

            $transaction->load('items');
            $transaction->recalculateTotals();

            $this->logActivity(
                $updater,
                'UPDATE_OUTGOING',
                $this->formatDescription($updater, 'UPDATE', 'OutgoingTransaction #'.$transaction->transaction_number),
                $transaction
            );

            return $transaction;
        });
    }

    public function approve(OutgoingTransaction $transaction, User $approver): void
    {
        if (! $transaction->canBeApproved()) {
            throw new DomainException('Only pending transactions can be approved.');
        }

        DB::transaction(function () use ($transaction, $approver): void {
            $transaction->loadMissing('items.product');

            foreach ($transaction->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    throw new ModelNotFoundException('One or more products in this transaction no longer exist.');
                }

                $this->stockAdjustments->decreaseStock(
                    $product,
                    (int) $item->quantity,
                    'outgoing_transaction',
                    $transaction,
                    $approver
                );
            }

            $transaction->update([
                'status' => OutgoingTransaction::STATUS_APPROVED,
                'approved_by' => $approver->id,
            ]);

            $this->logActivity(
                $approver,
                'APPROVE_OUTGOING',
                $this->formatDescription($approver, 'APPROVE', 'OutgoingTransaction #'.$transaction->transaction_number),
                $transaction
            );
        });
    }

    public function ship(OutgoingTransaction $transaction, User $actor): void
    {
        if (! $transaction->canBeShipped()) {
            throw new DomainException('Only approved transactions can be marked as shipped.');
        }

        $transaction->update([
            'status' => OutgoingTransaction::STATUS_SHIPPED,
        ]);

        $this->logActivity(
            $actor,
            'SHIP_OUTGOING',
            $this->formatDescription($actor, 'SHIP', 'OutgoingTransaction #'.$transaction->transaction_number),
            $transaction
        );
    }

    private function normalizeIndexFilters(array $filters): array
    {
        $allowedSorts = ['transaction_date', 'transaction_number', 'customer_name', 'status', 'total_items', 'total_quantity', 'total_amount', 'created_at'];
        $sort = $filters['sort'] ?? 'transaction_date';
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'transaction_date';
        }

        $direction = strtolower((string) ($filters['direction'] ?? 'desc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $status = $filters['status'] ?? [];
        $statusEnums = collect(is_array($status) ? $status : [$status])
            ->filter()
            ->map(static fn ($value) => $value instanceof OutgoingTransactionStatus ? $value : OutgoingTransactionStatus::tryFrom((string) $value))
            ->filter()
            ->values()
            ->all();

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => $statusEnums,
            'customer_ids' => array_values(array_filter((array) ($filters['customer_ids'] ?? []), static fn ($val) => $val !== null && $val !== '')),
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    private function applyStaffScope(Builder $query, ?User $user): void
    {
        if ($user !== null && $user->role === 'staff') {
            $query->where('created_by', $user->id);
        }
    }

    private function createItems(OutgoingTransaction $transaction, array $itemsData): void
    {
        $productIds = array_column($itemsData, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($itemsData as $itemData) {
            $quantity = (int) $itemData['quantity'];
            $product = $products[$itemData['product_id']] ?? null;

            if ($product === null) {
                throw new ModelNotFoundException('Product not found for outgoing transaction item.');
            }

            $unitPrice = $this->resolveUnitPrice($itemData, $product);

            $lineTotal = $quantity * $unitPrice;

            OutgoingTransactionItem::create([
                'outgoing_transaction_id' => $transaction->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }
    }

    private function resolveUnitPrice(array $itemData, Product $product): float
    {
        $unitPrice = isset($itemData['unit_price']) ? (float) $itemData['unit_price'] : 0.0;

        if ($unitPrice <= 0) {
            $unitPrice = (float) ($product->sale_price ?? 0);
        }

        return max(0.0, $unitPrice);
    }
}
