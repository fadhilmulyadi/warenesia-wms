<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check()
            && in_array(auth()->user()->role, ['admin', 'manager'], true);
    }

    /**
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'date_preset' => [
                'nullable',
                'string',
                Rule::in([
                    'today',
                    'yesterday',
                    'last_7_days',
                    'this_month',
                    'last_month',
                    'this_year',
                    'custom',
                ]),
            ],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'transaction_type' => ['nullable', 'string', Rule::in(['all', 'purchases', 'sales', 'restocks'])],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Normalize report filters with sane defaults.
     *
     * @return array<string, string|null>
     */
    public function filters(): array
    {
        return [
            'date_preset' => $this->input('date_preset', 'this_month'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'transaction_type' => $this->input('transaction_type', 'all'),
            'status' => $this->input('status'),
        ];
    }
}
