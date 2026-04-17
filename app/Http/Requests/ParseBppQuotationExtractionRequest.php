<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParseBppQuotationExtractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'quotation_extraction_text' => ['required', 'string'],
        ];
    }
}
