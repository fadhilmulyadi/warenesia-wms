<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'unit',
        'rack_location',
        'image_path',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function increaseStock(int $quantity): void
    {
        if ($quantity < 0) {
            throw new \InvalidArgumentException('Quantity must not be negative.');
        }

        if ($quantity === 0) {
            return;
        }

        $this->current_stock = (int) $this->current_stock + $quantity;
        $this->save();
    }

}