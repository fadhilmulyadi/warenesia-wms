<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StockAdjustmentService
{
    public function __construct(private readonly ActivityLogService $activityLogger) {}

    public function increaseStock(Product $product, int $qty, ?string $reason = null, ?Model $related = null, ?User $actor = null): StockAdjustment
    {
        $quantity = $this->assertPositiveQuantity($qty);

        return $this->adjust($product, $quantity, $reason ?? 'incoming_transaction', $related, $actor);
    }

    public function decreaseStock(Product $product, int $qty, ?string $reason = null, ?Model $related = null, ?User $actor = null): StockAdjustment
    {
        $quantity = $this->assertPositiveQuantity($qty);

        return $this->adjust($product, -$quantity, $reason ?? 'outgoing_transaction', $related, $actor);
    }

    private function adjust(Product $product, int $delta, string $reason, ?Model $related, ?User $actor): StockAdjustment
    {
        $reason = trim($reason);
        $actor ??= Auth::user();

        return DB::transaction(function () use ($product, $delta, $reason, $related, $actor): StockAdjustment {
            $lockedProduct = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $before = (int) $lockedProduct->current_stock;
            $after = $before + $delta;

            if ($after < 0) {
                throw new InsufficientStockException($lockedProduct->name);
            }

            $lockedProduct->update(['current_stock' => $after]);

            $adjustment = StockAdjustment::create([
                'product_id' => $lockedProduct->id,
                'before_stock' => $before,
                'after_stock' => $after,
                'quantity_change' => $delta,
                'reason' => $reason !== '' ? $reason : null,
                'related_type' => $related?->getMorphClass(),
                'related_id' => $related?->getKey(),
                'adjusted_by' => $actor?->id,
            ]);

            $this->activityLogger->log(
                $actor,
                $delta >= 0 ? 'STOCK_INCREASE' : 'STOCK_DECREASE',
                sprintf(
                    'Stock %s untuk produk "%s" berubah dari %d ke %d (%+d). Reason: %s',
                    $delta >= 0 ? 'increase' : 'decrease',
                    $lockedProduct->name,
                    $before,
                    $after,
                    $delta,
                    $reason !== '' ? $reason : '-'
                ),
                $related ?? $lockedProduct
            );

            return $adjustment;
        }, 3);
    }

    private function assertPositiveQuantity(int $qty): int
    {
        if ($qty <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        return $qty;
    }
}
