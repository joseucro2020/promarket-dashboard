<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Normaliza config a string (textarea) y available_types a array
        if (is_string($this->input('available_types'))) {
            $decoded = json_decode($this->input('available_types'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge(['available_types' => $decoded]);
            }
        }

        $status = $this->input('status');
        if ($status === 'on') {
            $this->merge(['status' => 1]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'available_types' => ['nullable', 'array', 'min:1'],
            'available_types.*' => ['string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'boolean'],
            'config' => ['nullable', 'string'],
            'payment_method_code' => ['required', 'string', 'max:255'],
            'icon_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $config = $this->input('config');
            if ($config !== null && $config !== '') {
                json_decode($config);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $validator->errors()->add('config', __('The config field must be valid JSON.'));
                }
            }
        });
    }
}
