<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RestockPrefill
{
    public static function forCreate(Request $request, Collection $products): array
    {
        $orderDate = now()->toDateString();
        $expectedDeliveryDate = now()->addDays(3)->toDateString();

        $productId = self::productIdFromRequest($request);
        $product = $productId ? $products->firstWhere('id', $productId) : null;

        $supplierId = null;
        $items = [];

        if ($product instanceof Product) {
            $supplierId = $product->supplier_id;
            $items[] = [
                'product_id' => $product->id,
                'quantity' => self::suggestedQuantity($product),
                'unit_cost' => (float) $product->purchase_price,
            ];
        }

        return [
            'order_date' => $orderDate,
            'expected_delivery_date' => $expectedDeliveryDate,
            'supplier_id' => $supplierId,
            'items' => $items,
        ];
    }

    private static function productIdFromRequest(Request $request): ?int
    {
        $productId = $request->query('product') ?? $request->query('product_id');

        if ($productId === null || $productId === '') {
            return null;
        }

        $number = filter_var($productId, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $number === null ? null : (int) $number;
    }

    private static function suggestedQuantity(Product $product): int
    {
        $currentStock = (int) ($product->current_stock ?? 0);
        $minimumStock = (int) ($product->min_stock ?? 0);
        $gap = $minimumStock - $currentStock;

        return $gap > 0 ? $gap : 1;
    }
}
