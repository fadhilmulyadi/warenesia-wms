<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category_id',
        'supplier_id',
        'description',
        'purchase_price',
        'sale_price',
        'min_stock',
        'current_stock',
        'unit_id',
        'rack_location',
        'image_path',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'min_stock' => 'integer',
        'current_stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function restockOrderItems(): HasMany
    {
        return $this->hasMany(RestockOrderItem::class);
    }

    public function getBarcodePayload(): string
    {
        return (string) $this->sku;
    }

    public function getBarcodeLabel(): string
    {
        return sprintf('%s (%s)', (string) $this->name, (string) $this->sku);
    }
}