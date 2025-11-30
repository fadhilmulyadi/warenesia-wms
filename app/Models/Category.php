<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image_path',
        'sku_prefix',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public static function generatePrefix(string $name): string
    {
        $words = array_values(array_filter(explode('-', Str::slug($name))));

        if (empty($words)) {
            return 'CAT';
        }

        $prefix = '';

        foreach ($words as $word) {
            $prefix .= substr($word, 0, 1);

            if (strlen($prefix) >= 3) {
                return strtoupper(substr($prefix, 0, 3));
            }
        }

        $lastWord = end($words);
        $remaining = substr($lastWord, 1);
        $prefix .= substr($remaining, 0, max(0, 3 - strlen($prefix)));

        $prefix = strtoupper(substr($prefix, 0, 3));

        return $prefix !== '' ? $prefix : 'CAT';
    }

    public static function ensureUniquePrefix(string $prefix, ?int $ignoreId = null): string
    {
        $basePrefix = strtoupper(substr($prefix, 0, 6));
        $candidate = $basePrefix;
        $counter = 1;

        while (self::query()
            ->when($ignoreId, static fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('sku_prefix', $candidate)
            ->exists()) {
            $suffix = (string) $counter;
            $availableLength = 6 - strlen($suffix);
            $candidate = substr($basePrefix, 0, max(1, $availableLength)) . $suffix;
            $counter++;
        }

        return $candidate;
    }

    public function setSkuPrefixAttribute(?string $value): void
    {
        $this->attributes['sku_prefix'] = strtoupper((string) ($value ?? ''));
    }
}
