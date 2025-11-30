<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
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
        $categoryId = $this->route('category')?->id ?? null;
        
        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'sku_prefix' => [
                'required',
                'string',
                'max:6',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('categories', 'sku_prefix')->ignore($categoryId),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image_path' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $categoryId = $this->route('category')?->id;
        $name = (string) $this->input('name');
        $prefix = $this->input('sku_prefix');

        if ($name && !$this->filled('sku_prefix')) {
            $prefix = Category::generatePrefix($name);
        }

        if ($prefix) {
            $prefix = strtoupper(trim((string) $prefix));
            $prefix = Category::ensureUniquePrefix($prefix, $categoryId);
        }

        $this->merge([
            'name' => $name,
            'sku_prefix' => $prefix,
        ]);
    }
}
