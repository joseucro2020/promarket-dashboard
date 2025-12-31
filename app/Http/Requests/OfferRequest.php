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
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
        ];
    }
}
