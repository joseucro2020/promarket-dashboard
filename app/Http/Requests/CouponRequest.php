<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $couponId = optional($this->route('coupon'))->id;

        return [
            'user_id' => ['required', 'exists:users,id'],
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($couponId)],
            'uses' => ['required', 'integer', 'min:1'],
            'discount_percentage' => ['required', 'numeric', 'between:0,100'],
            'first_purchase' => ['nullable', 'numeric', 'min:0'],
            'common_purchase' => ['nullable', 'numeric', 'min:0'],
            'recurrent_purchase' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
