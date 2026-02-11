<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:products,slug,'.$this->route('id'),
            'price_1' => 'required|numeric',
            'price_2' => 'required|numeric',
            'secondary_categories' => 'array',
            'secondary_categories.*' => 'distinct|exists:categories,id',
            'secondary_subcategories' => 'array',
            'secondary_subcategories.*' => 'distinct|exists:subcategories,id',
            'tags' => 'array',
            'tags.*' => 'distinct|exists:tags,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }
}
