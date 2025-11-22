<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutgoingTransaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SHIPPED = 'shipped';

    public const DEFAULT_PER_PAGE = 10;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'customer_name',
        'created_by',
        'approved_by',
        'status',
        'total_items',
        'total_quantity',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_items' => 'integer',
        'total_quantity' => 'integer',
        'total_amount' => 'decimal:2',
    ];

    public static function generateNextTransactionNumber(): string
    {
        $datePart = now()->format('Ymd');

        $lastTransaction = self::whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $lastSequence = 0;

        if ($lastTransaction !== null) {
            $parts = explode('-', $lastTransaction->transaction_number);
            $lastSequence = isset($parts[2]) ? (int) $parts[2] : 0;
        }

        $nextSequence = $lastSequence + 1;
        $sequencePart = str_pad((string) $nextSequence, 4, '0', STR_PAD_LEFT);

        return 'SO-' . $datePart . '-' . $sequencePart;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OutgoingTransactionItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isShipped(): bool
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    public function canBeApproved(): bool
    {
        return $this->isPending();
    }

    public function canBeShipped(): bool
    {
        return $this->isApproved();
    }
}