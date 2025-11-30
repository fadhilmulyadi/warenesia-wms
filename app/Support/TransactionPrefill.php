<?php

namespace App\Support;

use Illuminate\Http\Request;

class TransactionPrefill
{
    public static function forDashboard(Request $request): array
    {
        $base = self::baseFromInput($request);

        return [
            'type' => $base['type'],
            'supplier_id' => self::intOrNull($request->input('supplier_id')),
            'customer_name' => (string) $request->input('customer_name', ''),
            'product_id' => $base['product_id'],
            'quantity' => $base['quantity'],
            'unit_price' => self::floatOrNull($request->input('unit_price', $request->input('price'))),
            'unit_cost' => self::floatOrNull($request->input('unit_cost', $request->input('price'))),
        ];
    }

    public static function forPurchases(Request $request): array
    {
        $base = self::baseFromQuery($request);

        return [
            'product_id'  => $request->query('product_id'),
            'supplier_id' => $request->query('supplier_id'),
            'quantity'    => $request->query('quantity') ?? 1,
            'unit_cost'   => $request->query('unit_cost'),
        ];
    }

    public static function forSales(Request $request): array
    {
        $base = self::baseFromQuery($request);

        return [
            'product_id' => $base['product_id'],
            'quantity' => $base['quantity'],
            'customer_name' => (string) $request->query('customer_name', ''),
            'unit_price' => self::floatOrNull($request->query('unit_price', $request->query('price'))),
        ];
    }

    private static function baseFromQuery(Request $request): array
    {
        return [
            'type' => self::normalizeType((string) $request->query('type')),
            'product_id' => self::intOrNull($request->query('product_id')),
            'quantity' => self::quantityOrDefault($request->query('quantity')),
        ];
    }

    private static function baseFromInput(Request $request): array
    {
        return [
            'type' => self::normalizeType((string) $request->input('type')),
            'product_id' => self::intOrNull($request->input('product_id')),
            'quantity' => self::quantityOrDefault($request->input('quantity')),
        ];
    }

    private static function normalizeType(?string $type): string
    {
        return match ($type) {
            'incoming', 'purchase', 'purchases' => 'purchases',
            'outgoing', 'sale', 'sales' => 'sales',
            default => 'purchases',
        };
    }

    private static function intOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $number === null ? null : (int) $number;
    }

    private static function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_FLOAT, ['flags' => FILTER_NULL_ON_FAILURE]);

        return $number === null ? null : (float) $number;
    }

    private static function quantityOrDefault(mixed $value): int
    {
        $quantity = self::intOrNull($value);

        return $quantity !== null && $quantity > 0 ? $quantity : 1;
    }
}
