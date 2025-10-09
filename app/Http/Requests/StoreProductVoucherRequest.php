<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductVoucherRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'code' => 'required|string|unique:product_vouchers,code|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|string|in:active,inactive',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that either discount_amount or discount_percent is provided
            if (!$this->discount_amount && !$this->discount_percent) {
                $validator->errors()->add('discount', 'Either discount_amount or discount_percent must be provided');
            }

            // Validate that both are not provided
            if ($this->discount_amount && $this->discount_percent) {
                $validator->errors()->add('discount', 'Cannot provide both discount_amount and discount_percent');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required',
            'product_id.exists' => 'The selected product does not exist',
            'code.required' => 'Voucher code is required',
            'code.unique' => 'This voucher code already exists',
            'code.max' => 'Voucher code cannot exceed 50 characters',
            'discount_amount.numeric' => 'Discount amount must be a number',
            'discount_amount.min' => 'Discount amount must be at least 0',
            'discount_percent.numeric' => 'Discount percentage must be a number',
            'discount_percent.min' => 'Discount percentage must be at least 0',
            'discount_percent.max' => 'Discount percentage cannot exceed 100',
            'start_date.required' => 'Start date is required',
            'start_date.date' => 'Start date must be a valid date',
            'start_date.after_or_equal' => 'Start date must be today or later',
            'end_date.required' => 'End date is required',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after' => 'End date must be after start date',
            'status.required' => 'Status is required',
            'status.in' => 'Status must be either active or inactive',
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
            'product_id' => 'product',
            'code' => 'voucher code',
            'discount_amount' => 'discount amount',
            'discount_percent' => 'discount percentage',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'status' => 'status',
        ];
    }
}
