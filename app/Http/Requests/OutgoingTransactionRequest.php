<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class OutgoingTransactionRequest extends FormRequest
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
        $productsById = fn (): Collection => $this->resolveProductsById();

        return [
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function (string $attribute, mixed $value, callable $fail) use ($productsById): void {
                    if (! preg_match('/^items\.(\d+)\.quantity$/', $attribute, $matches)) {
                        return;
                    }

                    $index = (int) $matches[1];
                    $productId = (int) $this->input("items.$index.product_id");

                    if ($productId <= 0) {
                        return;
                    }

                    $product = $productsById()->get($productId);

                    if ($product === null) {
                        return;
                    }

                    $availableStock = (int) $product->current_stock;
                    $requestedQty = (int) $value;

                    if ($requestedQty > $availableStock) {
                        $fail("Stok untuk produk '{$product->name}' tidak mencukupi. Tersedia: {$availableStock}.");
                    }
                },
            ],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            
        ];
    }

    public function prepareForValidation(): void
    {
        $items = $this->input('items', []);

        $normalizedItems = [];

        foreach ($items as $item) {
            if (empty($item['product_id'])) {
                continue;
            }

            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
            if ($quantity <= 0) {
                continue;
            }

            $normalizedItems[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => isset($item['unit_price']) ? (float) $item['unit_price'] : 0.0,
            ];
        }

        $this->merge([
            'items' => $normalizedItems,
        ]);
    }

    private function resolveProductsById(): Collection
    {
        static $products = null;

        if ($products !== null) {
            return $products;
        }

        $productIds = collect($this->input('items', []))
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->all();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return $products;
    }
}
