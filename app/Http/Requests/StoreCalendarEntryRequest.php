<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'details' => ['nullable', 'string'],
            'month' => ['nullable', 'date_format:Y-m'],
        ];
    }
}
