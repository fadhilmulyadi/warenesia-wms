<?php

namespace App\Services;

use App\Enums\RestockStatus;
use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RestockService
{
    public function __construct(
        private readonly ActivityLogService $activityLogger,
        private readonly StockAdjustmentService $stockAdjustments,
        private readonly NumberGeneratorService $numberGenerator,
        private readonly IncomingTransactionService $incomingTransactions
    ) {}

    public function indexQuery(array $filters = [], ?User $user = null, bool $supplierContext = false): Builder
    {
        $filters = $this->normalizeIndexFilters($filters, $supplierContext);

        $query = RestockOrder::query()
            ->with(['supplier', 'createdBy', 'confirmedBy', 'ratingGivenBy', 'incomingTransaction']);

        if ($supplierContext && $user !== null) {
            $query->where('supplier_id', $user->id);
        }

        if ($filters['search'] !== '') {
            $query->where(function (Builder $searchQuery) use ($filters): void {
                $searchQuery->where('po_number', 'like', '%'.$filters['search'].'%');
                $searchQuery->orWhereHas('supplier', function (Builder $supplierQuery) use ($filters): void {
                    $supplierQuery->where('name', 'like', '%'.$filters['search'].'%');
                });
            });
        }

        if (! empty($filters['status'])) {
            $query->whereIn('status', array_map(static fn (RestockStatus $status) => $status->value, $filters['status']));
        }

        if ($filters['date_from']) {
            $query->whereDate('order_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $query->whereDate('order_date', '<=', $filters['date_to']);
        }

        return $query
            ->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');
    }

    public function create(array $validatedData, User $creator): RestockOrder
    {
        $itemsData = $this->extractItems($validatedData);

        return $this->runWithNumberRetry(function () use ($validatedData, $creator, $itemsData): RestockOrder {
            return DB::transaction(function () use ($validatedData, $creator, $itemsData): RestockOrder {
                $poNumber = $this->numberGenerator->generateDailySequence(
                    (new RestockOrder)->getTable(),
                    'po_number',
                    'PO',
                    4,
                    ''
                );

                $restockOrder = RestockOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $validatedData['supplier_id'],
                    'created_by' => $creator->id,
                    'confirmed_by' => null,
                    'order_date' => $validatedData['order_date'],
                    'expected_delivery_date' => $validatedData['expected_delivery_date'] ?? null,
                    'status' => RestockOrder::STATUS_PENDING,
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'total_amount' => 0,
                    'notes' => $validatedData['notes'] ?? null,
                ]);

                $this->createItems($restockOrder, $itemsData);

                $restockOrder->load('items');
                $restockOrder->recalculateTotals();

                $this->logActivity(
                    $creator,
                    'CREATE_RESTOCK',
                    sprintf('User "%s" membuat Restock #%s.', $creator->name ?? '-', $restockOrder->po_number),
                    $restockOrder
                );

                return $restockOrder;
            });
        });
    }

    public function markInTransit(RestockOrder $restock, ?User $actor = null): void
    {
        if (! $restock->canBeMarkedInTransit()) {
            throw new DomainException('Only confirmed orders can be marked as in transit.');
        }

        $restock->update([
            'status' => RestockOrder::STATUS_IN_TRANSIT,
        ]);

        $this->logActivity(
            $actor,
            'MARK_RESTOCK_IN_TRANSIT',
            sprintf('Restock #%s ditandai in transit.', $restock->po_number),
            $restock
        );
    }

    public function markReceived(RestockOrder $restock, ?User $actor = null): void
    {
        if (! $restock->canBeMarkedReceived()) {
            throw new DomainException('Only in transit orders can be marked as received.');
        }

        if ($actor === null) {
            throw new InvalidArgumentException('User is required to mark restock as received.');
        }

        DB::transaction(function () use ($restock, $actor): void {
            $restock->loadMissing('items');

            $restock->update([
                'status' => RestockOrder::STATUS_RECEIVED,
            ]);

            $incoming = $restock->incomingTransaction()->first();

            if ($incoming === null) {
                $incoming = $this->incomingTransactions->create(
                    $this->buildIncomingPayload($restock),
                    $actor
                );
            }

            if ($incoming->isPending()) {
                $this->incomingTransactions->verify($incoming, $actor);
            }

            $this->logActivity(
                $actor,
                'MARK_RESTOCK_RECEIVED',
                sprintf('Restock #%s ditandai received (menunggu proses masuk gudang).', $restock->po_number),
                $restock
            );
        });
    }

    public function cancel(RestockOrder $restock, ?User $actor = null): void
    {
        if (! $restock->canBeCancelled()) {
            throw new DomainException('Only pending or confirmed orders can be cancelled.');
        }

        $restock->update([
            'status' => RestockOrder::STATUS_CANCELLED,
        ]);

        $this->logActivity(
            $actor,
            'CANCEL_RESTOCK',
            sprintf('Restock #%s dibatalkan.', $restock->po_number),
            $restock
        );
    }

    public function supplierConfirm(RestockOrder $restock, User $supplier): void
    {
        if (! $restock->canBeConfirmedBySupplier()) {
            throw new DomainException('Only pending orders can be confirmed.');
        }

        if ((int) $restock->supplier_id !== (int) $supplier->id) {
            throw new DomainException('Supplier is not allowed to confirm this order.');
        }

        $restock->update([
            'status' => RestockOrder::STATUS_CONFIRMED,
            'confirmed_by' => $supplier->id,
        ]);

        $this->logActivity(
            $supplier,
            'SUPPLIER_CONFIRM_RESTOCK',
            sprintf('Supplier "%s" mengonfirmasi Restock #%s.', $supplier->name ?? '-', $restock->po_number),
            $restock
        );
    }

    public function supplierReject(RestockOrder $restock, User $supplier, ?string $reason = null): void
    {
        if (! $restock->canBeConfirmedBySupplier()) {
            throw new DomainException('Only pending orders can be rejected.');
        }

        if ((int) $restock->supplier_id !== (int) $supplier->id) {
            throw new DomainException('Supplier is not allowed to reject this order.');
        }

        $updates = [
            'status' => RestockOrder::STATUS_CANCELLED,
        ];

        $reason = trim((string) $reason);

        if ($reason !== '') {
            $existingNotes = (string) ($restock->notes ?? '');
            $notePrefix = $existingNotes !== '' ? $existingNotes.PHP_EOL : '';
            $updates['notes'] = $notePrefix.'Supplier rejection reason: '.$reason;
        }

        $restock->update($updates);

        $this->logActivity(
            $supplier,
            'SUPPLIER_REJECT_RESTOCK',
            sprintf('Supplier "%s" menolak Restock #%s.', $supplier->name ?? '-', $restock->po_number),
            $restock
        );
    }

    private function extractItems(array $validatedData): array
    {
        $items = $validatedData['items'] ?? [];

        if (count($items) === 0) {
            throw new InvalidArgumentException('At least one product must be added to the restock order.');
        }

        return $items;
    }

    private function createItems(RestockOrder $restockOrder, array $itemsData): void
    {
        foreach ($itemsData as $itemData) {
            $quantity = (int) $itemData['quantity'];
            $unitCost = isset($itemData['unit_cost']) ? (float) $itemData['unit_cost'] : 0.0;
            $lineTotal = $quantity * $unitCost;

            RestockOrderItem::create([
                'restock_order_id' => $restockOrder->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $lineTotal,
            ]);
        }
    }

    private function logActivity(?User $user, string $action, string $description, object $subject): void
    {
        $this->activityLogger->log(
            $user,
            $action,
            $description,
            $subject
        );
    }

    private function normalizeIndexFilters(array $filters, bool $supplierContext): array
    {
        $allowedSorts = ['po_number', 'order_date'];
        $sort = $filters['sort'] ?? 'order_date';
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'order_date';
        }

        $direction = strtolower((string) ($filters['direction'] ?? 'desc'));
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $statusFilter = $filters['status'] ?? [];
        $statusEnums = collect(is_array($statusFilter) ? $statusFilter : [$statusFilter])
            ->filter()
            ->map(static fn ($value) => $value instanceof RestockStatus ? $value : RestockStatus::tryFrom((string) $value))
            ->filter(static fn (?RestockStatus $status) => $status !== null)
            ->filter(static fn (RestockStatus $status) => $supplierContext
                ? in_array($status, [RestockStatus::PENDING, RestockStatus::CONFIRMED, RestockStatus::IN_TRANSIT, RestockStatus::RECEIVED], true)
                : true)
            ->values()
            ->all();

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'status' => $statusEnums,
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    private function runWithNumberRetry(callable $callback): mixed
    {
        $attempts = 0;
        $maxAttempts = 3;

        do {
            try {
                return $callback();
            } catch (QueryException $exception) {
                $attempts++;

                if (! $this->isUniqueConstraintViolation($exception) || $attempts >= $maxAttempts) {
                    throw $exception;
                }
            }
        } while ($attempts < $maxAttempts);

        return $callback();
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000';
    }

    private function buildIncomingPayload(RestockOrder $restock): array
    {
        $restock->loadMissing('items');

        return [
            'restock_order_id' => $restock->id,
            'transaction_date' => now()->toDateString(),
            'supplier_id' => $restock->supplier_id,
            'notes' => $restock->notes,
            'items' => $restock->items->map(static function (RestockOrderItem $item): array {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                ];
            })->all(),
        ];
    }
}
