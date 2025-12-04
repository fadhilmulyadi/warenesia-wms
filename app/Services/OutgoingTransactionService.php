<?php

namespace App\Services;

use App\Models\OutgoingTransaction;
use App\Models\OutgoingTransactionItem;
use App\Models\Product;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OutgoingTransactionService extends BaseTransactionService
{
    public function create(array $validatedData, User $creator): OutgoingTransaction
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

                $this->createItems($transaction, $itemsData);

                $transaction->load('items');
                $transaction->recalculateTotals();

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

    public function update(OutgoingTransaction $transaction, array $validatedData, User $updater): OutgoingTransaction
    {
        if (!$transaction->isPending()) {
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
                $this->formatDescription($updater, 'UPDATE', 'OutgoingTransaction #' . $transaction->transaction_number),
                $transaction
            );

            return $transaction;
        });
    }

    public function approve(OutgoingTransaction $transaction, User $approver): void
    {
        if (!$transaction->canBeApproved()) {
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

    public function ship(OutgoingTransaction $transaction, User $actor): void
    {
        if (!$transaction->canBeShipped()) {
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

    private function createItems(OutgoingTransaction $transaction, array $itemsData): void
    {
        $productIds = array_column($itemsData, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($itemsData as $itemData) {
            $quantity = (int) $itemData['quantity'];
            $unitPrice = isset($itemData['unit_price']) ? (float) $itemData['unit_price'] : 0.0;

            if ($unitPrice <= 0) {
                $product = $products[$itemData['product_id']] ?? null;

                if ($product) {
                    $unitPrice = (float) $product->price;
                }
            }

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
}
