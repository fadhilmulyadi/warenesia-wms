<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;
    private const EXPORT_CHUNK_SIZE = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $this->resolvePerPage(
            $request,
            self::DEFAULT_PER_PAGE,
            self::MAX_PER_PAGE
        );

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['name', 'sku', 'current_stock', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        $productsQuery = $this->buildProductIndexQuery($request, $sort, $direction);

        $products = $productsQuery
            ->paginate($perPage)
            ->withQueryString();

        $search = (string) $request->query('q', '');
        $categories = Category::whereHas('products')->orderBy('name')->get();

        return view('products.index', compact(
            'products',
            'search',
            'categories',
            'sort',
            'direction',
            'perPage'
        ));


    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();

        unset($data['image_path']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('products.show', compact('product', 'categories', 'suppliers', 'units'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers', 'units'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();

        unset($data['image_path']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        unset($data['image']);

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Product::class);

        [$sort, $direction] = $this->resolveSortAndDirection(
            $request,
            allowedSorts: ['name', 'sku', 'current_stock', 'created_at'],
            defaultSort: 'name',
            defaultDirection: 'asc'
        );

        $productQuery = $this->buildProductIndexQuery($request, $sort, $direction);

        $fileName = 'products-' . now()->format('Ymd-His') . '.csv';

        return CsvExporter::stream($fileName, function (\SplFileObject $output) use ($productQuery): void {
            $output->fputcsv([
                'ID',
                'Name',
                'SKU',
                'Category',
                'Supplier',
                'Purchase Price',
                'Sale Price',
                'Min Stock',
                'Current Stock',
                'Unit',
                'Rack Location',
                'Created At',
                'Updated At',
            ]);

            $productQuery
                ->chunk(self::EXPORT_CHUNK_SIZE, static function ($products) use ($output): void {
                    foreach ($products as $product) {
                        $output->fputcsv([
                            $product->id,
                            $product->name,
                            $product->sku,
                            $product->category->name ?? '',
                            $product->supplier->name ?? '',
                            (float) $product->purchase_price,
                            (float) $product->sale_price,
                            (int) $product->min_stock,
                            (int) $product->current_stock,
                            $product->unit,
                            $product->rack_location,
                            optional($product->created_at)->toDateTimeString(),
                            optional($product->updated_at)->toDateTimeString(),
                        ]);
                    }
                });
        });
    }

    private function buildProductIndexQuery(
        Request $request,
        string $sort,
        string $direction
    ): Builder {
        $search = (string) $request->query('q', '');

        $query = Product::query()
            ->with(['category', 'supplier']);

        $this->applySearch($query, $search, ['name', 'sku']);

        $this->applyFilters($query, $request, [
            'category_id' => 'category_id',

            'stock_status' => function (Builder $q, $value): void {
                $statuses = array_values(array_unique(array_filter((array) $value, static function ($val) {
                    return $val !== null && $val !== '';
                })));

                if (empty($statuses)) {
                    return;
                }

                $clauses = [];

                foreach ($statuses as $status) {
                    $status = strtolower($status);

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

                $q->where(function (Builder $stockQuery) use ($clauses): void {
                    foreach ($clauses as $clause) {
                        $stockQuery->orWhere($clause);
                    }
                });
            },
        ]);

        $query->orderBy($sort, $direction)
              ->orderBy('id');

        return $query;
    }
}
