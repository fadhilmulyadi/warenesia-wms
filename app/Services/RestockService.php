<?php

namespace App\Services;

use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RestockService
{
    public function __construct(
        private readonly ActivityLogService $activityLogger,
        private readonly StockAdjustmentService $stockAdjustments,
        private readonly NumberGeneratorService $numberGenerator
    ) {
    }

    public function create(array $validatedData, User $creator): RestockOrder
    {
        $itemsData = $this->extractItems($validatedData);

        return $this->runWithNumberRetry(function () use ($validatedData, $creator, $itemsData): RestockOrder {
            return DB::transaction(function () use ($validatedData, $creator, $itemsData): RestockOrder {
                $poNumber = $this->numberGenerator->generateDailySequence(
                    (new RestockOrder())->getTable(),
                    'po_number',
                    'PO',
                    4,
                    'order_date'
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
        if (!$restock->canBeMarkedInTransit()) {
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
        if (!$restock->canBeMarkedReceived()) {
            throw new DomainException('Only in transit orders can be marked as received.');
        }

        DB::transaction(function () use ($restock, $actor): void {
            $restock->loadMissing('items.product');

            foreach ($restock->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    throw new ModelNotFoundException('One or more products in this restock no longer exist.');
                }

                $this->stockAdjustments->increaseStock(
                    $product,
                    (int) $item->quantity,
                    'restock_received',
                    $restock
                );
            }

            $restock->update([
                'status' => RestockOrder::STATUS_RECEIVED,
            ]);

            $this->logActivity(
                $actor,
                'MARK_RESTOCK_RECEIVED',
                sprintf('Restock #%s ditandai received dan stok diperbarui.', $restock->po_number),
                $restock
            );
        });
    }

    public function cancel(RestockOrder $restock, ?User $actor = null): void
    {
        if (!$restock->canBeCancelled()) {
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
        if (!$restock->canBeConfirmedBySupplier()) {
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
        if (!$restock->canBeConfirmedBySupplier()) {
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
            $notePrefix = $existingNotes !== '' ? $existingNotes . PHP_EOL : '';
            $updates['notes'] = $notePrefix . 'Supplier rejection reason: ' . $reason;
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

    private function runWithNumberRetry(callable $callback): mixed
    {
        $attempts = 0;
        $maxAttempts = 3;

        do {
            try {
                return $callback();
            } catch (QueryException $exception) {
                $attempts++;

                if (!$this->isUniqueConstraintViolation($exception) || $attempts >= $maxAttempts) {
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
}