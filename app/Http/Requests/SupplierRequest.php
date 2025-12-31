<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'nombre_prove' => 'required|string|max:255',
            'email_prove' => 'nullable|email|max:255',
            'tlf_prove' => 'nullable|string|max:50',
            'pais_prove' => 'nullable|integer',
            'estado_prove' => 'nullable|integer',
            'muni_prove' => 'nullable|integer',
        ];
    }
}
