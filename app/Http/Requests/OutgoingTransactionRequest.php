<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OutgoingTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transaction_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
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
}
