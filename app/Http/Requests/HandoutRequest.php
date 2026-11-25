<?php

namespace App\Http\Requests;

use App\Enums\LcarsTheme;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HandoutRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:3000'],
            'theme' => ['required', Rule::enum(LcarsTheme::class)],
        ];
    }
}
