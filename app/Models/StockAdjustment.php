<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'before_stock',
        'after_stock',
        'quantity_change',
        'reason',
        'related_type',
        'related_id',
        'adjusted_by',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}