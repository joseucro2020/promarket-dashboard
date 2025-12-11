<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExchangeRateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'currency_from' => 'required|string|max:10',
            'currency_to' => 'required|string|max:10',
            'rate' => 'required|numeric',
            'date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ];
    }
}
