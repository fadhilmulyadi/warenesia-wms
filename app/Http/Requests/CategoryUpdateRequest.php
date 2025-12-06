<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Category|null $category */
        $category = $this->route('category');

        return $category
            ? $this->user()?->can('update', $category) ?? false
            : false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                'unique:categories,name,'.$categoryId,
            ],
            'sku_prefix' => [
                'nullable',
                'string',
                'max:6',
                'regex:/^[A-Z0-9]+$/',
            ],
            'description' => ['nullable', 'string'],
            'image_path' => ['sometimes', 'nullable', 'image', 'max:2048'],
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
