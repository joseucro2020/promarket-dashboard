<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products',
            'price_1' => 'required_if:variable,0|nullable|numeric|gt:price_2',
            'price_2' => 'required_if:variable,0|nullable|numeric',
            'category_id' => 'nullable|exists:categories,id',
            'secondary_categories' => 'array',
            'secondary_categories.*' => 'distinct|exists:categories,id',
            'secondary_subcategories' => 'array',
            'secondary_subcategories.*' => 'distinct|exists:subcategories,id',
            'tags' => 'array',
            'tags.*' => 'distinct|exists:tags,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'presentations' => 'array',
            'presentations.*.price' => 'required|numeric|gt:presentations.*.cost',
            'presentations.*.cost' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'price_1.gt' => __('El precio de venta debe ser mayor al costo.'),
            'presentations.*.price.gt' => __('El precio de venta de cada variante debe ser mayor a su costo.'),
        ];
    }
}
