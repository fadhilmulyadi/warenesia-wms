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

    public function setRackLocationAttribute($value): void
    {
        if (! $value) {
            $this->attributes['rack_location'] = null;

            return;
        }

        $value = strtoupper(trim((string) $value));
        $value = str_replace(' ', '', $value);

        if (preg_match('/^([A-Z])(\d{2})(\d{2})$/', $value, $matches)) {
            $zone = $matches[1];
            $rack = $matches[2];
            $bin = $matches[3];

            $this->attributes['rack_location'] = "{$zone}{$rack}-{$bin}";

            return;
        }

        if (preg_match('/^([A-Z])(\d{2})-(\d{1,2})$/', $value, $matches)) {
            $zone = $matches[1];
            $rack = $matches[2];
            $bin = str_pad($matches[3], 2, '0', STR_PAD_LEFT);

            $this->attributes['rack_location'] = "{$zone}{$rack}-{$bin}";

            return;
        }

        $this->attributes['rack_location'] = $value;
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
