<?php

namespace App\Http\Requests;

use App\Models\RestockOrder;
use Illuminate\Foundation\Http\FormRequest;

class RestockOrderRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $allowedRoles = ['admin', 'manager'];

        return in_array(auth()->user()->role, $allowedRoles, true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'integer',
                'min:'.RestockOrder::MIN_RATING,
                'max:'.RestockOrder::MAX_RATING,
            ],
            'rating_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Silakan pilih peringkat antara '.RestockOrder::MIN_RATING.' dan '.RestockOrder::MAX_RATING.'.',
            'rating.min' => 'Peringkat minimal harus '.RestockOrder::MIN_RATING.'.',
            'rating.max' => 'Peringkat tidak boleh melebihi '.RestockOrder::MAX_RATING.'.',
        ];
    }
}
