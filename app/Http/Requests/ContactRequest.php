<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Compatibilidad con la versión anterior: `slogan_english` -> `english_slogan`
        if ($this->has('slogan_english') && !$this->has('english_slogan')) {
            $this->merge(['english_slogan' => $this->input('slogan_english')]);
        }
    }

    public function rules(): array
    {
        return [
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email:rfc,dns|max:255',
            'facebook' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'slogan' => 'nullable|string|max:255',
            'english_slogan' => 'nullable|string|max:255',
        ];
    }
}
