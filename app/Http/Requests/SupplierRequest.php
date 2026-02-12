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
            'tipo_prove' => 'nullable|string|max:20',
            'proced_prove' => 'nullable|string|max:50',
            'id_prove' => 'nullable|string|max:50',
            'nombre_prove' => 'required|string|max:255',
            'direcc_prove' => 'nullable|string',
            'rsp_prove' => 'nullable|string|max:150',
            'email_prove' => 'nullable|email|max:255',
            'tlf_prove' => 'nullable|string|max:50',
            'postal_prove' => 'nullable|string|max:20',
            'status_prove' => 'required|integer',
            'pais_prove' => 'nullable|integer',
            'estado_prove' => 'nullable|integer',
            'muni_prove' => 'nullable|integer',
            'seller_name' => 'nullable|string|max:150',
            'seller_phone' => 'nullable|string|max:50',
        ];
    }
}
