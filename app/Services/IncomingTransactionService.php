<?php

namespace App\Services;

use App\Enums\IncomingTransactionStatus;
use App\Models\IncomingTransaction;
use App\Models\IncomingTransactionItem;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class IncomingTransactionService extends BaseTransactionService
{
    public function indexQuery(array $filters = [], ?User $user = null): Builder
    {
        $filters = $this->normalizeIndexFilters($filters);

        $query = IncomingTransaction::query()
            ->with(['supplier', 'createdBy', 'verifiedBy']);

        if ($filters['search'] !== '') {
            $query->where(function (Builder $searchQuery) use ($filters): void {
                $searchQuery->where('transaction_number', 'like', '%'.$filters['search'].'%');

                $searchQuery->orWhereHas('supplier', function (Builder $supplierQuery) use ($filters): void {
                    $supplierQuery->where('name', 'like', '%'.$filters['search'].'%');
                });
            });
        }

        if (! empty($filters['status'])) {
            $query->whereIn('status', array_map(static fn (IncomingTransactionStatus $status) => $status->value, $filters['status']));
        }

        if ($filters['supplier_id']) {
            $query->where('supplier_id', $filters['supplier_id']);
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

    public function create(array $validatedData, User $creator): IncomingTransaction
    {
        $itemsData = $this->extractItems($validatedData);

        return $this->runWithNumberRetry(function () use ($validatedData, $creator, $itemsData): IncomingTransaction {
            return DB::transaction(function () use ($validatedData, $creator, $itemsData): IncomingTransaction {
                $transactionNumber = $this->numberGenerator->generateDailySequence(
                    (new IncomingTransaction)->getTable(),
                    'transaction_number',
                    'PO'
                );

                $transaction = IncomingTransaction::create([
                    'transaction_number' => $transactionNumber,
                    'restock_order_id' => $validatedData['restock_order_id'] ?? null,
                    'transaction_date' => $validatedData['transaction_date'],
                    'supplier_id' => $validatedData['supplier_id'],
                    'created_by' => $creator->id,
                    'verified_by' => null,
                    'status' => IncomingTransaction::STATUS_PENDING,
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
                    'CREATE_INCOMING',
                    $this->formatDescription($creator, 'CREATE', 'IncomingTransaction #'.$transaction->transaction_number),
                    $transaction
                );

                return $transaction;
            });
        });
    }

    public function update(IncomingTransaction $transaction, array $validatedData, User $updater): IncomingTransaction
    {
        if (! $transaction->isPending()) {
            throw new DomainException('Only pending transactions can be updated.');
        }

        $itemsData = $this->extractItems($validatedData);

        return DB::transaction(function () use ($transaction, $validatedData, $itemsData, $updater) {
            $transaction->update([
                'transaction_date' => $validatedData['transaction_date'],
                'supplier_id' => $validatedData['supplier_id'],
                'notes' => $validatedData['notes'] ?? null,
            ]);

            $transaction->items()->delete();

            $this->createItems($transaction, $itemsData);

            $transaction->load('items');
            $transaction->recalculateTotals();

            $this->logActivity(
                $updater,
                'UPDATE_INCOMING',
                $this->formatDescription($updater, 'UPDATE', 'IncomingTransaction #'.$transaction->transaction_number),
                $transaction
            );

            return $transaction;
        });
    }

    public function verify(IncomingTransaction $transaction, User $verifier): void
    {
        if (! $transaction->canBeVerified()) {
            throw new DomainException('Only pending transactions can be verified.');
        }

        DB::transaction(function () use ($transaction, $verifier): void {
            $transaction->loadMissing('items.product');

            foreach ($transaction->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    throw new ModelNotFoundException('One or more products in this transaction no longer exist.');
                }

                $this->stockAdjustments->increaseStock(
                    $product,
                    (int) $item->quantity,
                    'incoming_transaction',
                    $transaction,
                    $verifier
                );
            }

            $transaction->update([
                'status' => IncomingTransaction::STATUS_VERIFIED,
                'verified_by' => $verifier->id,
            ]);

            $this->logActivity(
                $verifier,
                'VERIFY_INCOMING',
                $this->formatDescription($verifier, 'VERIFY', 'IncomingTransaction #'.$transaction->transaction_number),
                $transaction
            );
        });
    }

    public function reject(IncomingTransaction $transaction, User $verifier, ?string $reason): void
    {
        if (! $transaction->canBeRejected()) {
            throw new DomainException('Only pending transactions can be rejected.');
        }

        $updates = [
            'status' => IncomingTransaction::STATUS_REJECTED,
            'verified_by' => $verifier->id,
        ];

        $reason = trim((string) $reason);

        if ($reason !== '') {
            $existingNotes = trim((string) ($transaction->notes ?? ''));
            $updates['notes'] = $existingNotes !== ''
                ? $existingNotes.PHP_EOL.'Rejection reason: '.$reason
                : 'Rejection reason: '.$reason;
        }

        $transaction->update($updates);

        $this->logActivity(
            $verifier,
            'REJECT_INCOMING',
            $this->formatDescription($verifier, 'REJECT', 'IncomingTransaction #'.$transaction->transaction_number),
            $transaction
        );
    }

    private function normalizeIndexFilters(array $filters): array
    {
        $allowedSorts = ['transaction_date', 'transaction_number', 'status', 'total_items', 'total_quantity', 'total_amount', 'created_at'];
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
            ->map(static fn ($value) => $value instanceof IncomingTransactionStatus ? $value : IncomingTransactionStatus::tryFrom((string) $value))
            ->filter()
            ->values()
            ->all();

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => $statusEnums,
            'supplier_id' => $filters['supplier_id'] ?? null,
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

    public function complete(IncomingTransaction $transaction, User $actor): void
    {
        if (! $transaction->canBeCompleted()) {
            throw new DomainException('Only verified transactions can be marked as completed.');
        }

        $transaction->update([
            'status' => IncomingTransaction::STATUS_COMPLETED,
        ]);

        $this->logActivity(
            $actor,
            'COMPLETE_INCOMING',
            $this->formatDescription($actor, 'COMPLETE', 'IncomingTransaction #'.$transaction->transaction_number),
            $transaction
        );
    }

    private function createItems(IncomingTransaction $transaction, array $itemsData): void
    {
        $productIds = array_column($itemsData, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($itemsData as $itemData) {
            $quantity = (int) $itemData['quantity'];
            $unitCost = isset($itemData['unit_cost']) ? (float) $itemData['unit_cost'] : 0.0;

            if ($unitCost <= 0) {
                $product = $products[$itemData['product_id']] ?? null;

                if ($product) {
                    $unitCost = (float) $product->purchase_price;
                }
            }

            $lineTotal = $quantity * $unitCost;

            IncomingTransactionItem::create([
                'incoming_transaction_id' => $transaction->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $lineTotal,
            ]);
        }
    }
}
