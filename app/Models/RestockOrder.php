<?php

namespace App\Models;

use App\Enums\RestockStatus;
use App\Services\NumberGeneratorService;
use App\Services\Support\TransactionTotalsCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestockOrder extends Model
{
    use HasFactory;

    public function recalculateTotals(): void
    {
        $totals = TransactionTotalsCalculator::calculate($this->items);

        $this->total_items = $totals['total_items'];
        $this->total_quantity = $totals['total_quantity'];
        $this->total_amount = number_format($totals['total_amount'], 2, '.', '');

        $this->save();
    }

    public const STATUS_PENDING = RestockStatus::PENDING->value;

    public const STATUS_CONFIRMED = RestockStatus::CONFIRMED->value;

    public const STATUS_IN_TRANSIT = RestockStatus::IN_TRANSIT->value;

    public const STATUS_RECEIVED = RestockStatus::RECEIVED->value;

    public const STATUS_CANCELLED = RestockStatus::CANCELLED->value;

    public const MIN_RATING = 1;

    public const MAX_RATING = 5;

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
        'rating',
        'rating_notes',
        'rating_given_by',
        'rating_given_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
        'total_amount' => 'decimal:2',
        'rating' => 'integer',
        'rating_given_at' => 'datetime',
        'status' => RestockStatus::class,
    ];

    public static function generateNextPurchaseOrderNumber(): string
    {
        return app(NumberGeneratorService::class)->generateDailySequence(
            (new static)->getTable(),
            'po_number',
            self::PURCHASE_ORDER_PREFIX,
            self::SEQUENCE_PAD_LENGTH
        );
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

    public function ratingGivenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rating_given_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RestockOrderItem::class);
    }

    public static function statusOptions(): array
    {
        return collect(RestockStatus::cases())
            ->mapWithKeys(fn (RestockStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    public function getStatusLabelAttribute(): string
    {
        $status = $this->status instanceof RestockStatus
            ? $this->status
            : RestockStatus::tryFrom((string) $this->status);

        return $status?->label()
            ?? ucfirst((string) $this->status);
    }

    public function isPending(): bool
    {
        return $this->status === RestockStatus::PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === RestockStatus::CONFIRMED;
    }

    public function isInTransit(): bool
    {
        return $this->status === RestockStatus::IN_TRANSIT;
    }

    public function isReceived(): bool
    {
        return $this->status === RestockStatus::RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === RestockStatus::CANCELLED;
    }

    public function canBeConfirmed(): bool
    {
        return $this->isPending();
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

    public function hasRating(): bool
    {
        return $this->rating !== null;
    }

    public function canBeRated(): bool
    {
        return $this->isReceived();
    }

    public function isRatedBy(?User $user): bool
    {
        if ($user === null || $this->rating_given_by === null) {
            return false;
        }

        return (int) $this->rating_given_by === (int) $user->id;
    }

    public function incomingTransaction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(IncomingTransaction::class);
    }
}
