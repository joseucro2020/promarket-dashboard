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
            // Accept either explicit type or discount_mode from the form
            'type' => 'required_without:discount_mode|string',
            'discount_mode' => 'required_without:type|in:quantity,amount,count',
            'start' => 'nullable|date',
            'end' => 'nullable|date|after_or_equal:start',
            'limit' => 'nullable|integer|min:0',
            // Conditional fields
            'quantity_products' => 'nullable|integer|min:0|required_if:discount_mode,quantity|required_if:type,quantity_product',
            'min_amount' => 'nullable|numeric|min:0|required_if:discount_mode,amount|required_if:type,minimum_purchase',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
}
