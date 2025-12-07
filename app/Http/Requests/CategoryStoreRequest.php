<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Category::class) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80', 'unique:categories,name'],
            'sku_prefix' => [
                'nullable',
                'string',
                'max:6',
                'regex:/^[A-Z0-9]+$/',
            ],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'image', 'max:2048'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'sku_prefix' => $this->sanitizePrefix($this->input('sku_prefix')),
        ]);
    }

    private function sanitizePrefix(?string $prefix): ?string
    {
        if ($prefix === null) {
            return null;
        }

        $clean = strtoupper(trim((string) $prefix));
        $clean = preg_replace('/[^A-Z0-9]/', '', $clean);

        return $clean ?: null;
    }
}
