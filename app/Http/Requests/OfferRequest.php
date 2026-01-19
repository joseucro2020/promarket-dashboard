<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OfferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'percentage' => 'required|numeric|min:0|max:100',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'products' => 'required|array|min:1',
            'products.*' => 'integer|exists:products,id',
        ];
    }
}
