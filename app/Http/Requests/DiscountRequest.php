<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'type' => 'required|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'limit' => 'nullable|integer|min:0',
        ];
    }
}
