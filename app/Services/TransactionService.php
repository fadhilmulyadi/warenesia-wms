<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\IncomingTransaction;
use App\Models\IncomingTransactionItem;
use App\Models\OutgoingTransaction;
use App\Models\OutgoingTransactionItem;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(private readonly ActivityLogService $activityLogger)
    {
    }

    public function createIncoming(array $validatedData, User $creator): IncomingTransaction
    {
        $itemsData = $validatedData['items'] ?? [];

        if (count($itemsData) === 0) {
            throw new InvalidArgumentException('At least one product must be added to the transaction.');
        }

        return DB::transaction(function () use ($validatedData, $creator, $itemsData): IncomingTransaction {
            $transactionNumber = GeneratorService::generateDailySequence(
                IncomingTransaction::class,
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

            $this->activityLogger->log(
                $creator,
                'CREATE_INCOMING',
                $this->formatDescription($creator, 'CREATE', 'IncomingTransaction #' . $transaction->transaction_number),
                $transaction
            );

            return $transaction;
        });
    }

    public function verifyIncoming(IncomingTransaction $transaction, User $verifier): void
    {
        if (! $transaction->canBeVerified()) {
            throw new DomainException('Only pending transactions can be verified.');
        }

        DB::transaction(function () use ($transaction, $verifier): void {
            $transaction->loadMissing('items.product');

            foreach ($transaction->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    continue;
                }

                $product->increaseStock((int) $item->quantity);
            }

            $transaction->update([
                'status' => IncomingTransaction::STATUS_VERIFIED,
                'verified_by' => $verifier->id,
            ]);

            $this->activityLogger->log(
                $verifier,
                'VERIFY_INCOMING',
                $this->formatDescription($verifier, 'VERIFY', 'IncomingTransaction #' . $transaction->transaction_number),
                $transaction
            );
        });
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

        $this->activityLogger->log(
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

        $this->activityLogger->log(
            $actor,
            'COMPLETE_INCOMING',
            $this->formatDescription($actor, 'COMPLETE', 'IncomingTransaction #' . $transaction->transaction_number),
            $transaction
        );
    }

    public function createOutgoing(array $validatedData, User $creator): OutgoingTransaction
    {
        $itemsData = $validatedData['items'] ?? [];

        if (count($itemsData) === 0) {
            throw new InvalidArgumentException('At least one product must be added to the transaction.');
        }

        return DB::transaction(function () use ($validatedData, $creator, $itemsData): OutgoingTransaction {
            $transactionNumber = GeneratorService::generateDailySequence(
                OutgoingTransaction::class,
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

            $this->activityLogger->log(
                $creator,
                'CREATE_OUTGOING',
                $this->formatDescription($creator, 'CREATE', 'OutgoingTransaction #' . $transaction->transaction_number),
                $transaction
            );

            return $transaction;
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

                if (! $product->hasSufficientStock((int) $item->quantity)) {
                    throw new InsufficientStockException($product->name);
                }
            }

            foreach ($transaction->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    continue;
                }

                $product->decreaseStock((int) $item->quantity);
            }

            $transaction->update([
                'status' => OutgoingTransaction::STATUS_APPROVED,
                'approved_by' => $approver->id,
            ]);

            $this->activityLogger->log(
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

        $this->activityLogger->log(
            $actor,
            'SHIP_OUTGOING',
            $this->formatDescription($actor, 'SHIP', 'OutgoingTransaction #' . $transaction->transaction_number),
            $transaction
        );
    }

    private function createIncomingItems(IncomingTransaction $transaction, array $itemsData): array
    {
        $totalItems = count($itemsData);
        $totalQuantity = 0;
        $totalAmount = 0.0;

        foreach ($itemsData as $itemData) {
            $quantity = (int) $itemData['quantity'];
            $unitCost = isset($itemData['unit_cost']) ? (float) $itemData['unit_cost'] : 0.0;
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
}
