<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IncomingTransaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    public const DEFAULT_PER_PAGE = 10;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'supplier_id',
        'created_by',
        'verified_by',
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

        return 'PO-' . $datePart . '-' . $sequencePart;
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IncomingTransactionItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeVerified(): bool
    {
        return $this->isPending();
    }

    public function canBeRejected(): bool
    {
        return $this->isPending();
    }

    public function canBeCompleted(): bool
    {
        return $this->isVerified();
    }
}
