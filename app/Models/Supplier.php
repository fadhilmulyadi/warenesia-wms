<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    public const DEFAULT_PER_PAGE = 10;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'tax_number',
        'address',
        'city',
        'country',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function restockOrders(): HasMany
    {
        return $this->hasMany(RestockOrder::class);
    }
}
