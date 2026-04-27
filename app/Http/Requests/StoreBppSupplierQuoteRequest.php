<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBppSupplierQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'supplier_name' => ['required', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'supplier_address' => ['nullable', 'string'],
            'total_price' => ['required', 'numeric', 'gte:0'],
            'delivery_period' => ['required', 'string', 'max:255'],
            'validity_period' => ['required', 'string', 'max:255'],
            'quotation_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
