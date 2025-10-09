<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStockLogRequest extends FormRequest
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
            'product_variant_id' => 'required|exists:product_variants,id',
            'change_type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'changed_by' => 'required|exists:users,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Product variant is required.',
            'product_variant_id.exists' => 'Selected product variant does not exist.',
            'change_type.required' => 'Change type is required.',
            'change_type.in' => 'Change type must be either increase or decrease.',
            'quantity.required' => 'Quantity is required.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'changed_by.required' => 'User who made the change is required.',
            'changed_by.exists' => 'Selected user does not exist.',
        ];
    }
}
