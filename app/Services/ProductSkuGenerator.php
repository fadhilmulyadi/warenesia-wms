<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductSkuGenerator
{
    public function generate(int $categoryId): string
    {
        /** @var Category|null $category */
        $category = Category::find($categoryId);

        if (!$category) {
            throw new ModelNotFoundException('Kategori tidak ditemukan untuk pembuatan SKU.');
        }

        if (!$category->sku_prefix) {
            $category->sku_prefix = Category::ensureUniquePrefix(
                Category::generatePrefix($category->name),
                $category->id
            );
            $category->save();
        }

        $prefix = $category->sku_prefix;

        $latestSku = Product::where('sku', 'like', $prefix . '-%')
            ->orderByDesc('id')
            ->value('sku');

        $nextNumber = $this->resolveNextNumber($latestSku);

        return sprintf('%s-%04d', $prefix, $nextNumber);
    }

    private function resolveNextNumber(?string $latestSku): int
    {
        if (!$latestSku) {
            return 1;
        }

        $parts = explode('-', $latestSku);
        $lastChunk = end($parts);
        $lastNumber = (int) preg_replace('/\D/', '', (string) $lastChunk);

        return $lastNumber + 1;
    }
}
