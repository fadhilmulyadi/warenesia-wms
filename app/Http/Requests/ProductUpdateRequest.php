<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Product|null $product */
        $product = $this->route('product');

        return $product
            ? $this->user()?->can('update', $product) ?? false
            : false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'description' => ['nullable', 'string'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'current_stock' => ['sometimes', 'integer', 'min:0'],
            'unit_id' => ['required', 'exists:units,id'],
            'rack_location' => ['nullable', 'regex:/^[A-Z][0-9]{2}-[0-9]{2}$/'],
            'image' => ['sometimes', 'nullable', 'image', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama produk',
            'category_id' => 'kategori',
            'supplier_id' => 'supplier',
            'purchase_price' => 'harga beli',
            'sale_price' => 'harga jual',
            'min_stock' => 'stok minimum',
            'current_stock' => 'stok saat ini',
            'unit_id' => 'satuan',
            'rack_location' => 'lokasi rak',
        ];
    }

    public function messages(): array
    {
        return [
            'rack_location.regex' => 'Format lokasi rak tidak valid. Gunakan format ZRR-BB, misalnya A12-03.',
        ];
    }
}
