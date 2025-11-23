<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Support\CsvExporter;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    private const DEFAULT_PER_PAGE = 10;
    private const EXPORT_CHUNK_SIZE = 200;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $productQuery = $this->buildProductIndexQuery($request);

        $products = $productQuery
            ->paginate(self::DEFAULT_PER_PAGE)
            ->withQueryString();

        $search = (string) $request->query('q', '');

        return view('products.index', compact('products', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'sku'           => ['required', 'string', 'max:255', 'unique:products,sku'],
            'category_id'   => ['required', 'exists:categories,id'],
            'supplier_id'   => ['nullable', 'exists:suppliers,id'],
            'purchase_price'=> ['required', 'numeric', 'min:0'],
            'sale_price'    => ['required', 'numeric', 'min:0'],
            'min_stock'     => ['required', 'integer', 'min:0'],
            'current_stock' => ['required', 'integer', 'min:0'],
            'unit'          => ['required', 'string', 'max:20'],
            'rack_location' => ['nullable', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
        ]);

            $product = Product::create($validated);

        return redirect()
            ->route('products.edit', $product)
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

        return view('products.show', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully.');
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
            ->with('status', 'Product deleted successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('export', Product::class);

        $productQuery = $this->buildProductIndexQuery($request);
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
                ->orderBy('name')
                ->orderBy('id')
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

    private function buildProductIndexQuery(Request $request): Builder
    {
        $search = (string) $request->query('q', '');

        return Product::query()
            ->with(['category', 'supplier'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name');
    }
}
