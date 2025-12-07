<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncomingTransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'incoming_transaction_id',
        'product_id',
        'quantity',
        'unit_cost',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function incomingTransaction(): BelongsTo
    {
        return $this->belongsTo(IncomingTransaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
