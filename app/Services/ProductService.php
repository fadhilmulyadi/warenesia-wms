<?php

namespace App\Services;

use App\Models\Category;
use App\Models\IncomingTransactionItem;
use App\Models\OutgoingTransactionItem;
use App\Models\Product;
use App\Models\RestockOrderItem;
use App\Models\StockAdjustment;
use App\Models\Unit;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ProductService
{
    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;

    public function __construct(
        private readonly CategoryService $categories,
        private readonly NumberGeneratorService $numbers
    ) {
    }

    public function index(array $filters = []): LengthAwarePaginator
    {
        $query = $this->query($filters);
        $perPage = $this->resolvePerPage($filters['per_page'] ?? null);

        return $query->paginate($perPage)->withQueryString();
    }

    public function query(array $filters = []): Builder
    {
        $filters = $this->normaliseFilters($filters);

        $query = Product::query()
            ->with(['category', 'supplier', 'unit']);

        if ($filters['search'] !== '') {
            $keyword = $filters['search'];
            $query->where(function (Builder $q) use ($keyword): void {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('sku', 'like', '%' . $keyword . '%');
            });
        }

        if ($filters['category_id']) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['stock_status'])) {
            $this->applyStockFilter($query, $filters['stock_status']);
        }

        $query->orderBy($filters['sort'], $filters['direction'])
            ->orderBy('id');

        return $query;
    }

    public function create(array $data): Product
    {
        return $this->runWithSkuRetry(function () use ($data): Product {
            return DB::transaction(function () use ($data): Product {
                $category = $this->resolveCategory($data['category_id'] ?? null);
                $unitId = $this->resolveUnitId($data['unit_id'] ?? null);

                $sku = $this->generateSku($category);

                $payload = $this->buildPayload($data, $sku, $category->id, $unitId);

                return Product::create($payload);
            });
        });
    }

    public function update(Product $product, array $data): Product
    {
        $categoryId = $data['category_id'] ?? $product->category_id;
        $unitId = $this->resolveUnitId($data['unit_id'] ?? $product->unit_id);

        $category = $this->resolveCategory($categoryId);
        $shouldRegenerateSku = (int) $category->id !== (int) $product->category_id;
        $shouldRegenerateSku = $shouldRegenerateSku || !$product->sku;

        $updater = function () use ($product, $data, $category, $unitId, $shouldRegenerateSku): Product {
            $sku = $shouldRegenerateSku
                ? $this->generateSku($category)
                : $product->sku;

            $payload = $this->buildPayload(
                $data,
                $sku,
                $category->id,
                $unitId,
                $product
            );

            $product->update($payload);

            return $product->refresh();
        };

        return $shouldRegenerateSku
            ? $this->runWithSkuRetry(fn () => DB::transaction($updater))
            : DB::transaction($updater);
    }

    public function delete(Product $product): void
    {
        if ($this->hasRelations($product)) {
            throw new DomainException('Produk tidak dapat dihapus karena sudah dipakai di transaksi atau restock.');
        }

        $image = $product->image_path;

        $product->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }
    }

    private function buildPayload(
        array $data,
        string $sku,
        int $categoryId,
        int $unitId,
        ?Product $product = null
    ): array {
        $imagePath = $this->storeImage(
            $data['image'] ?? $data['image_path'] ?? null,
            $product?->image_path
        );

        $payload = [
            'name' => trim((string) $data['name']),
            'sku' => $sku,
            'category_id' => $categoryId,
            'supplier_id' => $this->normalizeNullableId($data['supplier_id'] ?? null),
            'description' => $data['description'] ?? null,
            'purchase_price' => $this->toFloat($data['purchase_price'] ?? null),
            'sale_price' => $this->toFloat($data['sale_price'] ?? null),
            'min_stock' => $this->toInt($data['min_stock'] ?? null),
            'current_stock' => $this->toInt($data['current_stock'] ?? $product?->current_stock ?? 0),
            'unit_id' => $unitId,
            'rack_location' => $data['rack_location'] ?? null,
            'image_path' => $imagePath,
        ];

        return $payload;
    }

    private function generateSku(Category $category): string
    {
        $prefix = $this->categories->ensurePrefix($category);

        return $this->numbers->generateSequentialNumber(
            (new Product())->getTable(),
            'sku',
            $prefix,
            4,
            static function ($query) use ($category): void {
                $query->where('category_id', $category->id);
            }
        );
    }

    private function resolveCategory(int|string|null $categoryId): Category
    {
        if (!$categoryId) {
            throw new InvalidArgumentException('Category is required.');
        }

        $category = Category::find($categoryId);

        if (!$category) {
            throw new InvalidArgumentException('Kategori tidak ditemukan.');
        }

        return $category;
    }

    private function resolveUnitId(int|string|null $unitId): int
    {
        if (!$unitId) {
            throw new InvalidArgumentException('Unit produk wajib diisi.');
        }

        $unit = Unit::find($unitId);

        if (!$unit) {
            throw new InvalidArgumentException('Satuan produk tidak ditemukan.');
        }

        return (int) $unit->id;
    }

    private function storeImage(UploadedFile|string|null $image = null, ?string $oldImage = null): ?string
    {
        if (!$image instanceof UploadedFile) {
            return $oldImage;
        }

        $path = $image->store('products', 'public');

        if ($oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return $path;
    }

    private function hasRelations(Product $product): bool
    {
        return RestockOrderItem::where('product_id', $product->id)->exists()
            || IncomingTransactionItem::where('product_id', $product->id)->exists()
            || OutgoingTransactionItem::where('product_id', $product->id)->exists()
            || StockAdjustment::where('product_id', $product->id)->exists();
    }

    private function applyStockFilter(Builder $query, array $statuses): void
    {
        $statuses = array_values(array_filter($statuses, static fn ($val) => $val !== null && $val !== ''));

        if (empty($statuses)) {
            return;
        }

        $clauses = [];

        foreach ($statuses as $status) {
            $status = strtolower((string) $status);

            if ($status === 'available') {
                $clauses[] = static function (Builder $stock): void {
                    $stock->where('current_stock', '>', 0);
                };
            } elseif ($status === 'low') {
                $clauses[] = static function (Builder $stock): void {
                    $stock->where('current_stock', '>', 0)
                        ->whereColumn('current_stock', '<=', 'min_stock');
                };
            } elseif ($status === 'out') {
                $clauses[] = static function (Builder $stock): void {
                    $stock->where('current_stock', '<=', 0);
                };
            }
        }

        if (empty($clauses)) {
            return;
        }

        $query->where(function (Builder $stockQuery) use ($clauses): void {
            foreach ($clauses as $clause) {
                $stockQuery->orWhere($clause);
            }
        });
    }

    private function normaliseFilters(array $filters): array
    {
        $allowedSorts = ['name', 'sku', 'current_stock', 'created_at'];
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
            'category_id' => $filters['category_id'] ?? null,
            'stock_status' => (array) ($filters['stock_status'] ?? []),
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

    private function runWithSkuRetry(callable $callback): mixed
    {
        $attempts = 0;
        $maxAttempts = 3;

        do {
            try {
                return $callback();
            } catch (QueryException $exception) {
                $attempts++;

                if ((string) $exception->getCode() !== '23000' || $attempts >= $maxAttempts) {
                    throw $exception;
                }
            }
        } while ($attempts < $maxAttempts);

        return $callback();
    }

    private function normalizeNullableId(int|string|null $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function toInt(int|string|null $value, int $default = 0): int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    private function toFloat(float|int|string|null $value, float $default = 0.0): float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        return (float) $value;
    }
}