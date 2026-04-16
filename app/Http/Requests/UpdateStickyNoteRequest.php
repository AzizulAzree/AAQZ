<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStickyNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string', 'max:5000'],
            'position_x' => ['required', 'integer', 'min:0', 'max:10000'],
            'position_y' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_collapsed' => ['required', 'boolean'],
        ];
    }
}
