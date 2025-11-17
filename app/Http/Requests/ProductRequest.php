<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'sku'            => ['required', 'string', 'max:100', 'unique:products,sku,' . $productId],
            'category_id'    => ['required', 'exists:categories,id'],
            'supplier_id'    => ['nullable', 'exists:suppliers,id'],
            'description'    => ['nullable', 'string'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price'     => ['required', 'numeric', 'min:0'],
            'min_stock'      => ['required', 'integer', 'min:0'],
            'current_stock'  => ['required', 'integer', 'min:0'],
            'unit'           => ['required', 'string', 'max:20'],
            'rack_location'  => ['nullable', 'string', 'max:50'],
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
}
