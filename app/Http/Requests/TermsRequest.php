<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TermsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms_text' => ['required', 'string'],
            'terms_english' => ['required', 'string'],
            'conditions_text' => ['required', 'string'],
            'conditions_english' => ['required', 'string'],
        ];
    }
}
