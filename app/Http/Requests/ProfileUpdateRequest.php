<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\UserColor;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'color' => UserColor::normalize($this->input('color')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        if (Schema::hasColumn((new User)->getTable(), 'color')) {
            $rules['color'] = [
                'required',
                'regex:/^#[0-9A-F]{6}$/',
                Rule::unique(User::class, 'color')->ignore($this->user()->id),
            ];
        }

        return $rules;
    }
}
