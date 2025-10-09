<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
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
            'shipping_address' => 'sometimes|string|max:500',
            'courier_name' => 'sometimes|string|max:100',
            'tracking_number' => 'sometimes|string|max:100',
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
            'shipping_address.string' => 'Shipping address must be a string',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters',
            'courier_name.string' => 'Courier name must be a string',
            'courier_name.max' => 'Courier name cannot exceed 100 characters',
            'tracking_number.string' => 'Tracking number must be a string',
            'tracking_number.max' => 'Tracking number cannot exceed 100 characters',
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
            'shipping_address' => 'shipping address',
            'courier_name' => 'courier name',
            'tracking_number' => 'tracking number',
        ];
    }
}
