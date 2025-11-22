<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestockOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_RECEIVED  = 'received';
    public const STATUS_CANCELLED = 'cancelled';

    public const DEFAULT_PER_PAGE = 10;

    private const PURCHASE_ORDER_PREFIX = 'PO';
    private const SEQUENCE_PAD_LENGTH = 4;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'created_by',
        'confirmed_by',
        'order_date',
        'expected_delivery_date',
        'status',
        'total_items',
        'total_quantity',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public static function generateNextPurchaseOrderNumber(): string
    {
        $datePart = now()->format('Ymd');

        $lastOrder = self::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $lastSequence = 0;

        if ($lastOrder !== null) {
            $parts = explode('-', $lastOrder->po_number);

            if (isset($parts[2])) {
                $lastSequence = (int) $parts[2];
            }
        }

        $nextSequence = $lastSequence + 1;

        $sequencePart = str_pad(
            (string) $nextSequence,
            self::SEQUENCE_PAD_LENGTH,
            '0',
            STR_PAD_LEFT
        );

        return self::PURCHASE_ORDER_PREFIX . '-' . $datePart . '-' . $sequencePart;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestockOrderItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isInTransit(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeConfirmedBySupplier(): bool
    {
        return $this->isPending();
    }

    public function canBeMarkedInTransit(): bool
    {
        return $this->isConfirmed();
    }

    public function canBeMarkedReceived(): bool
    {
        return $this->isInTransit();
    }

    public function canBeCancelled(): bool
    {
        return $this->isPending() || $this->isConfirmed();
    }
}
