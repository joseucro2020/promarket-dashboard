<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecialCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:0,1'],
            'slider_quantity' => ['nullable', 'integer', 'min:1'],
            'tipo_order' => ['nullable'],
            'tipo_special' => ['nullable'],
            'products' => ['nullable', 'string'],
        ];
    }
}
