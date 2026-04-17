<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBppAppendixRowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'appendix_type' => ['required', 'string', Rule::in(['c2', 'c3', 'c4'])],
            'item_spesifikasi' => ['required', 'string'],
            'kuantiti' => ['required', 'numeric', 'gt:0'],
            'unit_ukuran' => ['required', 'string', 'max:255'],
            'harga_seunit' => ['required', 'numeric', 'gte:0'],
        ];
    }
}
