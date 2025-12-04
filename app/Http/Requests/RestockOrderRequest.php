<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestockOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check()
            && in_array(auth()->user()->role, ['admin', 'manager'], true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Setidaknya satu produk harus ditambahkan ke pesanan restock.',
            'items.min' => 'Setidaknya satu produk harus ditambahkan ke pesanan restock.',
        ];
    }

    public function prepareForValidation(): void
    {
        $items = $this->input('items', []);

        $normalizedItems = [];

        foreach ($items as $rawItem) {
            if (empty($rawItem['product_id'])) {
                continue;
            }

            $quantity = isset($rawItem['quantity']) ? (int) $rawItem['quantity'] : 0;

            if ($quantity <= 0) {
                continue;
            }

            $normalizedItems[] = [
                'product_id' => (int) $rawItem['product_id'],
                'quantity' => $quantity,
                'unit_cost' => isset($rawItem['unit_cost'])
                    ? (float) $rawItem['unit_cost']
                    : 0.0,
            ];
        }

        $this->merge([
            'items' => $normalizedItems,
        ]);
    }
}
