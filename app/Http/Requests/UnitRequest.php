<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
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
