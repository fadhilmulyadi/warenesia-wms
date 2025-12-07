<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Unit|null $unit */
        $unit = $this->route('unit');

        return $unit
            ? $this->user()?->can('update', $unit) ?? false
            : false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $unitId = $this->route('unit')?->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('units', 'name')->ignore($unitId),
            ],
            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
