<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => 'required|string',
            'shipping_address' => 'required|string|max:500',
            'shipping_rate_id' => 'required|exists:shipping_rates,id',
            'courier_name' => 'nullable|string|max:100',
            'global_voucher_codes' => 'nullable|array',
            'global_voucher_codes.*' => 'string|exists:global_vouchers,code',
            'product_voucher_codes' => 'nullable|array',
            'product_voucher_codes.*' => 'string|exists:product_vouchers,code',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method.required' => 'Payment method is required',
            'payment_method.in' => 'Payment method must be one of: bank_transfer, credit_card, e_wallet, cash_on_delivery',
            'shipping_address.required' => 'Shipping address is required',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters',
            'shipping_rate_id.required' => 'Shipping rate is required',
            'shipping_rate_id.exists' => 'The selected shipping rate does not exist',
            'courier_name.max' => 'Courier name cannot exceed 100 characters',
            'global_voucher_codes.array' => 'Global voucher codes must be an array',
            'global_voucher_codes.*.exists' => 'One or more global voucher codes are invalid',
            'product_voucher_codes.array' => 'Product voucher codes must be an array',
            'product_voucher_codes.*.exists' => 'One or more product voucher codes are invalid',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'payment_method' => 'payment method',
            'shipping_address' => 'shipping address',
            'shipping_rate_id' => 'shipping rate',
            'courier_name' => 'courier name',
            'global_voucher_codes' => 'global voucher codes',
            'product_voucher_codes' => 'product voucher codes',
        ];
    }
}
