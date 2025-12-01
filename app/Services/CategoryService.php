<?php

namespace App\Services;

use App\Models\Category;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryService
{
    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;

    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query($filters);
        $perPage = $this->resolvePerPage($filters['per_page'] ?? null);

        return $query->paginate($perPage)->withQueryString();
    }

    public function query(array $filters = []): Builder
    {
        $filters = $this->normaliseFilters($filters);

        $query = Category::query()
            ->withCount('products');

        if ($filters['search'] !== '') {
            $keyword = $filters['search'];
            $query->where(function (Builder $q) use ($keyword): void {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('sku_prefix', 'like', '%' . $keyword . '%');
            });
        }

        if ($filters['name']) {
            $query->where('name', $filters['name']);
        }

        $query->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');

        return $query;
    }

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data): Category {
            $category = new Category();
            $category->name = trim((string) $data['name']);
            $category->description = $data['description'] ?? null;
            $category->sku_prefix = $this->preparePrefix(
                $category->name,
                $data['sku_prefix'] ?? null,
                null
            );
            $category->image_path = $this->storeImage($data['image_path'] ?? $data['image'] ?? null);
            $category->save();

            return $category;
        });
    }

    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data): Category {
            $updates = [
                'name' => trim((string) ($data['name'] ?? $category->name)),
                'description' => $data['description'] ?? null,
            ];

            $updates['sku_prefix'] = $this->preparePrefix(
                $updates['name'],
                $data['sku_prefix'] ?? $category->sku_prefix,
                $category->id
            );

            if (array_key_exists('image_path', $data) || array_key_exists('image', $data)) {
                $updates['image_path'] = $this->storeImage(
                    $data['image_path'] ?? $data['image'] ?? null,
                    $category->image_path
                );
            }

            $category->update($updates);

            return $category->refresh();
        });
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new DomainException('Kategori tidak dapat dihapus karena masih memiliki produk terkait.');
        }

        $oldImage = $category->image_path;

        $category->delete();

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }
    }

    public function generatePrefix(string $name, ?int $ignoreId = null): string
    {
        $base = $this->buildBasePrefix($name);
        $candidate = $base;
        $suffixIndex = 0;

        while ($this->prefixExists($candidate, $ignoreId)) {
            $candidate = $base . $this->suffixFromIndex($suffixIndex);
            $suffixIndex++;
        }

        return $candidate;
    }

    public function ensurePrefix(Category $category): string
    {
        if ($category->sku_prefix) {
            return $category->sku_prefix;
        }

        $prefix = $this->generatePrefix($category->name, $category->id);
        $category->forceFill(['sku_prefix' => $prefix])->save();

        return $prefix;
    }

    private function preparePrefix(string $name, ?string $provided, ?int $ignoreId = null): string
    {
        $prefix = $this->sanitizePrefix($provided);

        if ($prefix === '') {
            $prefix = $this->generatePrefix($name, $ignoreId);
        } elseif ($this->prefixExists($prefix, $ignoreId)) {
            $prefix = $this->resolveCollision($prefix, $ignoreId);
        }

        return $prefix;
    }

    private function resolveCollision(string $base, ?int $ignoreId = null): string
    {
        $base = str_pad(substr($base, 0, 3), 3, 'X');
        $suffixIndex = 0;
        $candidate = $base;

        while ($this->prefixExists($candidate, $ignoreId)) {
            $candidate = $base . $this->suffixFromIndex($suffixIndex);
            $suffixIndex++;
        }

        return $candidate;
    }

    private function prefixExists(string $prefix, ?int $ignoreId = null): bool
    {
        return Category::query()
            ->when($ignoreId, static function (Builder $query, $ignoreId): void {
                $query->whereKeyNot($ignoreId);
            })
            ->where('sku_prefix', $prefix)
            ->exists();
    }

    private function storeImage(UploadedFile|string|null $image = null, ?string $oldImage = null): ?string
    {
        if (!$image instanceof UploadedFile) {
            return $oldImage;
        }

        $path = $image->store('categories', 'public');

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $path;
    }

    private function buildBasePrefix(string $name): string
    {
        $slug = Str::slug($name, '');
        $letters = strtoupper(preg_replace('/[^A-Z0-9]/', '', $slug));

        if ($letters === '') {
            $letters = 'CAT';
        }

        return substr(str_pad($letters, 3, 'X'), 0, 3);
    }

    private function suffixFromIndex(int $index): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = strlen($alphabet);

        $suffix = '';
        $current = $index;

        do {
            $suffix = $alphabet[$current % $length] . $suffix;
            $current = intdiv($current, $length) - 1;
        } while ($current >= 0);

        return $suffix;
    }

    private function sanitizePrefix(?string $prefix): string
    {
        $prefix = strtoupper(trim((string) $prefix));
        $prefix = preg_replace('/[^A-Z0-9]/', '', $prefix);

        return substr($prefix, 0, 4);
    }

    private function normaliseFilters(array $filters): array
    {
        $allowedSorts = ['name', 'sku_prefix', 'products_count', 'created_at'];
        $sort = $filters['sort'] ?? 'name';
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $direction = strtolower((string) ($filters['direction'] ?? 'asc'));
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        return [
            'search' => trim((string) ($filters['search'] ?? '')),
            'name' => $filters['name'] ?? null,
            'sort' => $sort,
            'direction' => $direction,
        ];
    }

    private function resolvePerPage(?int $perPage): int
    {
        if ($perPage === null || $perPage <= 0) {
            return self::DEFAULT_PER_PAGE;
        }

        return min($perPage, self::MAX_PER_PAGE);
    }
}