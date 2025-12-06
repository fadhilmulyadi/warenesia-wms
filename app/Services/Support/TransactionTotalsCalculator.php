<?php

namespace App\Services\Support;

class TransactionTotalsCalculator
{
    /**
     * Calculate totals from a collection of items.
     *
     * @return array{total_items: int, total_quantity: int, total_amount: float}
     */
    public static function calculate(iterable $items): array
    {
        $totalItems = 0;
        $totalQuantity = 0;
        $totalAmount = 0.0;

        foreach ($items as $item) {
            $totalItems++;
            $qty = (int) $item->quantity;

            $total = $item->line_total ?? ($qty * (float) ($item->unit_cost ?? $item->unit_price ?? 0));

            $totalQuantity += $qty;
            $totalAmount += (float) $total;
        }

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'total_amount' => $totalAmount,
        ];
    }
}
