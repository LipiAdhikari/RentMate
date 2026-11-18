<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonthlyRecordRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'between:1,12'],
            'bs_year' => ['nullable', 'integer', 'between:2070,2100'],
            'bs_month' => ['nullable', 'integer', 'between:1,12'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'prev_electricity_reading' => ['required', 'numeric', 'min:0'],
            'curr_electricity_reading' => ['required', 'numeric', 'gte:prev_electricity_reading'],
            'electricity_rate' => ['required', 'numeric', 'min:0'],
            'total_water_bill' => ['required', 'numeric', 'min:0'],
            'tenant_water_percentage' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
            'expenses' => ['nullable', 'array'],
            'expenses.*.title' => ['required_with:expenses.*.amount', 'string', 'max:255'],
            'expenses.*.amount' => ['required_with:expenses.*.title', 'numeric', 'min:0.01'],
            'expenses.*.category' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'curr_electricity_reading.gte' => 'Current meter reading cannot be lower than the previous meter reading.',
            'rent_amount.required' => 'Monthly rent amount is required.',
            'electricity_rate.required' => 'Electricity rate per unit is required.',
            'total_water_bill.required' => 'Total water bill amount is required.',
        ];
    }
}
