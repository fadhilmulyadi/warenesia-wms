<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use GuzzleHttp\Handler\Proxy;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('q');

        $query = Product::with(['category', 'supplier']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        $products = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.products.index', compact('products', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
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
            ->route('admin.products.edit', $product)
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return $this->edit($product);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers  = Supplier::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
