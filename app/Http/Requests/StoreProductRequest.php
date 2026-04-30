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
            'type' => 'nullable|in:simple,variable,bulk',
            'variable' => 'nullable|integer|in:0,1,2',
            'price_1' => 'required_if:variable,0|required_if:type,simple|required_if:type,bulk|nullable|numeric|gt:price_2',
            'price_2' => 'required_if:variable,0|required_if:type,simple|required_if:type,bulk|nullable|numeric',
            'bulk_unit' => 'nullable|required_if:variable,2|required_if:type,bulk|in:Mg,Gr,Kg,Oz,Lb,Ml,L,Mt',
            'bulk_min_sale' => 'nullable|integer|min:0',
            'bulk_step' => 'nullable|integer|min:1',
            'umbral' => 'nullable|numeric|min:0',
            'sku' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'collection_id' => 'nullable|exists:collections,id',
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
