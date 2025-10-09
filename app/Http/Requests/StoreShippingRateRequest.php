<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingRateRequest extends FormRequest
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
            'province_id' => 'required|exists:provinces,id',
            'courier' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0|max:9999999999.99',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'province_id.required' => 'Province is required.',
            'province_id.exists' => 'Selected province does not exist.',
            'courier.required' => 'Courier name is required.',
            'courier.string' => 'Courier name must be a string.',
            'courier.max' => 'Courier name must not exceed 50 characters.',
            'rate.required' => 'Shipping rate is required.',
            'rate.numeric' => 'Shipping rate must be a number.',
            'rate.min' => 'Shipping rate must be at least 0.',
            'rate.max' => 'Shipping rate is too large.',
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
            'province_id' => 'province',
            'courier' => 'courier name',
            'rate' => 'shipping rate',
        ];
    }
}
