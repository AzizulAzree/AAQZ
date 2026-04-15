<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEntryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $enabled = $this->boolean('follow_up_enabled');

        $this->merge([
            'follow_up_enabled' => $enabled,
            'follow_up_days' => $enabled && $this->filled('follow_up_days')
                ? $this->input('follow_up_days')
                : null,
        ]);
    }

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
            'follow_up_enabled' => ['required', 'boolean'],
            'follow_up_days' => ['nullable', 'integer', 'min:1', 'max:30', 'required_if:follow_up_enabled,true'],
            'month' => ['nullable', 'date_format:Y-m'],
        ];
    }
}
