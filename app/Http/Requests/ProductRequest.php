<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Services\ProductSkuGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name'           => ['required', 'string', 'max:255'],
            'sku'            => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'category_id'    => ['required', 'exists:categories,id'],
            'supplier_id'    => ['nullable', 'exists:suppliers,id'],
            'description'    => ['nullable', 'string'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price'     => ['required', 'numeric', 'min:0'],
            'min_stock'      => ['required', 'integer', 'min:0'],
            'current_stock'  => ['required', 'integer', 'min:0'],
            'unit'           => ['required', 'string', 'max:80', Rule::exists('units', 'name')],
            'rack_location'  => ['nullable', 'string', 'max:50'],
            'image'          => ['nullable', 'image', 'max:2048'],
            'image_path'     => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name'           => 'nama produk',
            'sku'            => 'SKU',
            'category_id'    => 'kategori',
            'supplier_id'    => 'supplier',
            'purchase_price' => 'harga beli',
            'sale_price'     => 'harga jual',
            'min_stock'      => 'stok minimum',
            'current_stock'  => 'stok saat ini',
            'unit'           => 'unit',
            'rack_location'  => 'lokasi rak',
        ];
    }

    protected function prepareForValidation(): void
    {
        $product = $this->route('product');
        $categoryId = $this->input('category_id');
        $sku = $this->input('sku');

        if ($product) {
            $sku = $product->sku;
        } elseif (!$sku && $categoryId && Category::whereKey($categoryId)->exists()) {
            $sku = app(ProductSkuGenerator::class)
                ->generate((int) $categoryId);
        }

        if ($sku) {
            $sku = strtoupper(trim((string) $sku));
        }

        $this->merge([
            'sku' => $sku,
        ]);
    }
}
