<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestockOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restock_order_id',
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

    public function restockOrder(): BelongsTo
    {
        return $this->belongsTo(RestockOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}