<?php

namespace App\Services;

use App\Models\IncomingTransaction;
use App\Models\IncomingTransactionItem;
use App\Models\OutgoingTransaction;
use App\Models\OutgoingTransactionItem;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(
        private readonly ActivityLogService $activityLogger,
        private readonly StockAdjustmentService $stockAdjustments,
        private readonly NumberGeneratorService $numberGenerator
    ) {
    }

    public function createIncoming(array $validatedData, User $creator): IncomingTransaction
    {
        $itemsData = $this->extractItems($validatedData);

        return $this->runWithNumberRetry(function () use ($validatedData, $creator, $itemsData): IncomingTransaction {
            return DB::transaction(function () use ($validatedData, $creator, $itemsData): IncomingTransaction {
                $transactionNumber = $this->numberGenerator->generateDailySequence(
                    (new IncomingTransaction())->getTable(),
                    'transaction_number',
                    'PO'
                );

                $transaction = IncomingTransaction::create([
                    'transaction_number' => $transactionNumber,
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

                $totals = $this->createIncomingItems($transaction, $itemsData);

                $transaction->update([
                    'total_items' => $totals['total_items'],
                    'total_quantity' => $totals['total_quantity'],
                    'total_amount' => $totals['total_amount'],
                ]);

                $this->logActivity(
                    $creator,
                    'CREATE_INCOMING',
                    $this->formatDescription($creator, 'CREATE', 'IncomingTransaction #' . $transaction->transaction_number),
                    $transaction
                );

                return $transaction;
            });
        });
    }

    public function approveIncoming(IncomingTransaction $transaction, User $verifier): void
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
                    $transaction
                );
            }

            $transaction->update([
                'status' => IncomingTransaction::STATUS_VERIFIED,
                'verified_by' => $verifier->id,
            ]);

            $this->logActivity(
                $verifier,
                'VERIFY_INCOMING',
                $this->formatDescription($verifier, 'VERIFY', 'IncomingTransaction #' . $transaction->transaction_number),
                $transaction
            );
        });
    }

    public function verifyIncoming(IncomingTransaction $transaction, User $verifier): void
    {
        $this->approveIncoming($transaction, $verifier);
    }

    public function rejectIncoming(IncomingTransaction $transaction, User $verifier, ?string $reason): void
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
                ? $existingNotes . PHP_EOL . 'Rejection reason: ' . $reason
                : 'Rejection reason: ' . $reason;
        }

        $transaction->update($updates);

        $this->logActivity(
            $verifier,
            'REJECT_INCOMING',
            $this->formatDescription($verifier, 'REJECT', 'IncomingTransaction #' . $transaction->transaction_number),
            $transaction
        );
    }

    public function completeIncoming(IncomingTransaction $transaction, User $actor): void
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
            $this->formatDescription($actor, 'COMPLETE', 'IncomingTransaction #' . $transaction->transaction_number),
            $transaction
        );
    }

    public function createOutgoing(array $validatedData, User $creator): OutgoingTransaction
    {
        $itemsData = $this->extractItems($validatedData);

        return $this->runWithNumberRetry(function () use ($validatedData, $creator, $itemsData): OutgoingTransaction {
            return DB::transaction(function () use ($validatedData, $creator, $itemsData): OutgoingTransaction {
                $transactionNumber = $this->numberGenerator->generateDailySequence(
                    (new OutgoingTransaction())->getTable(),
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

                $totals = $this->createOutgoingItems($transaction, $itemsData);

                $transaction->update([
                    'total_items' => $totals['total_items'],
                    'total_quantity' => $totals['total_quantity'],
                    'total_amount' => $totals['total_amount'],
                ]);

                $this->logActivity(
                    $creator,
                    'CREATE_OUTGOING',
                    $this->formatDescription($creator, 'CREATE', 'OutgoingTransaction #' . $transaction->transaction_number),
                    $transaction
                );

                return $transaction;
            });
        });
    }

    public function approveOutgoing(OutgoingTransaction $transaction, User $approver): void
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
                    $transaction
                );
            }

            $transaction->update([
                'status' => OutgoingTransaction::STATUS_APPROVED,
                'approved_by' => $approver->id,
            ]);

            $this->logActivity(
                $approver,
                'APPROVE_OUTGOING',
                $this->formatDescription($approver, 'APPROVE', 'OutgoingTransaction #' . $transaction->transaction_number),
                $transaction
            );
        });
    }

    public function shipOutgoing(OutgoingTransaction $transaction, User $actor): void
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
            $this->formatDescription($actor, 'SHIP', 'OutgoingTransaction #' . $transaction->transaction_number),
            $transaction
        );
    }

    private function extractItems(array $validatedData): array
    {
        $items = $validatedData['items'] ?? [];

        if (count($items) === 0) {
            throw new InvalidArgumentException('At least one product must be added to the transaction.');
        }

        return $items;
    }

    private function createIncomingItems(IncomingTransaction $transaction, array $itemsData): array
    {
        $totalItems = count($itemsData);
        $totalQuantity = 0;
        $totalAmount = 0.0;

        $productIds = array_column($itemsData, 'product_id');
        $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

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

            $totalQuantity += $quantity;
            $totalAmount += $lineTotal;

            IncomingTransactionItem::create([
                'incoming_transaction_id' => $transaction->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'line_total' => $lineTotal,
            ]);
        }

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
        ];
    }

    private function createOutgoingItems(OutgoingTransaction $transaction, array $itemsData): array
    {
        $totalItems = count($itemsData);
        $totalQuantity = 0;
        $totalAmount = 0.0;

        foreach ($itemsData as $itemData) {
            $quantity = (int) $itemData['quantity'];
            $unitPrice = isset($itemData['unit_price']) ? (float) $itemData['unit_price'] : 0.0;
            $lineTotal = $quantity * $unitPrice;

            $totalQuantity += $quantity;
            $totalAmount += $lineTotal;

            OutgoingTransactionItem::create([
                'outgoing_transaction_id' => $transaction->id,
                'product_id' => $itemData['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ]);
        }

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
        ];
    }

    public function updateIncoming(IncomingTransaction $transaction, array $validatedData, User $updater): IncomingTransaction
    {
        if (! $transaction->isPending()) {
            throw new DomainException('Only pending transactions can be updated.');
        }

        $itemsData = $this->extractItems($validatedData);

        return DB::transaction(function () use ($transaction, $validatedData, $itemsData, $updater) {
            $transaction->update([
                'transaction_date' => $validatedData['transaction_date'],
                'supplier_id'      => $validatedData['supplier_id'],
                'notes'            => $validatedData['notes'] ?? null,
            ]);

            $transaction->items()->delete();

            $totals = $this->createIncomingItems($transaction, $itemsData);

            $transaction->update([
                'total_items'    => $totals['total_items'],
                'total_quantity' => $totals['total_quantity'],
                'total_amount'   => $totals['total_amount'],
            ]);

            $this->logActivity(
                $updater,
                'UPDATE_INCOMING',
                $this->formatDescription($updater, 'UPDATE', 'IncomingTransaction #' . $transaction->transaction_number),
                $transaction
            );

            return $transaction;
        });
    }

    /**
     * Memperbarui transaksi barang keluar (Sales).
     */
    public function updateOutgoing(OutgoingTransaction $transaction, array $validatedData, User $updater): OutgoingTransaction
    {
        if (! $transaction->isPending()) {
            throw new DomainException('Only pending transactions can be updated.');
        }

        $itemsData = $this->extractItems($validatedData);

        return DB::transaction(function () use ($transaction, $validatedData, $itemsData, $updater) {
            $transaction->update([
                'transaction_date' => $validatedData['transaction_date'],
                'customer_name'    => $validatedData['customer_name'], // Sales pakai customer_name
                'notes'            => $validatedData['notes'] ?? null,
            ]);

            $transaction->items()->delete();

            $totals = $this->createOutgoingItems($transaction, $itemsData);

            $transaction->update([
                'total_items'    => $totals['total_items'],
                'total_quantity' => $totals['total_quantity'],
                'total_amount'   => $totals['total_amount'],
            ]);

            $this->logActivity(
                $updater,
                'UPDATE_OUTGOING',
                $this->formatDescription($updater, 'UPDATE', 'OutgoingTransaction #' . $transaction->transaction_number),
                $transaction
            );

            return $transaction;
        });
    }

    private function formatDescription(User $user, string $action, string $subjectLabel): string
    {
        $userName = $user->name ?? 'Unknown';

        return sprintf(
            'User "%s" melakukan "%s" pada "%s".',
            $userName,
            strtoupper($action),
            $subjectLabel
        );
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
}