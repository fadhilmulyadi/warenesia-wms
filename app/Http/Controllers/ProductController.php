<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HasIndexQueryHelpers;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\ProductService;
use App\Support\CsvExporter;
use Illuminate\Http\Request;
use DomainException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use HasIndexQueryHelpers;

    private const DEFAULT_PER_PAGE = 10;
    private const MAX_PER_PAGE = 250;
    private const EXPORT_CHUNK_SIZE = 200;

    public function __construct(private readonly ProductService $products)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

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

        $filters = [
            'search' => (string) $request->query('q', ''),
            'category_id' => $request->query('category_id'),
            'stock_status' => (array) $request->query('stock_status', []),
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => $perPage,
        ];

        $products = $this->products->index($filters);

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
    public function store(ProductStoreRequest $request)
    {
        $this->authorize('create', Product::class);

        try {
            $this->products->create($request->validated());
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['store' => $exception->getMessage()]);
        }

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

        $product->loadMissing(['category', 'supplier', 'unit']);

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
    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        try {
            $this->products->update($product, $request->validated());
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->withErrors(['update' => $exception->getMessage()]);
        }

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

        try {
            $this->products->delete($product);
        } catch (DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

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

        $productQuery = $this->products->query([
            'search' => (string) $request->query('q', ''),
            'category_id' => $request->query('category_id'),
            'stock_status' => (array) $request->query('stock_status', []),
            'sort' => $sort,
            'direction' => $direction,
        ]);

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
                            $product->unit->name ?? '',
                            $product->rack_location,
                            optional($product->created_at)->toDateTimeString(),
                            optional($product->updated_at)->toDateTimeString(),
                        ]);
                    }
                });
        });
    }
}