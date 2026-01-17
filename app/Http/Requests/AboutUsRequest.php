<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AboutUsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'texto' => 'required|string|max:10000',
            'english' => 'required|string|max:10000',
            'mision' => 'required|string|max:10000',
            'mision_english' => 'required|string|max:10000',
            'vision' => 'required|string|max:10000',
            'vision_english' => 'required|string|max:10000',
        ];
    }
}
