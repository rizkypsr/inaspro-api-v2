<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreGlobalVoucherRequest extends FormRequest
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
            'code' => 'required|string|unique:global_vouchers,code|max:50',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
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

            // If max_discount_amount is provided, discount_percent should also be provided
            if ($this->max_discount_amount && !$this->discount_percent) {
                $validator->errors()->add('max_discount_amount', 'Max discount amount can only be used with percentage-based discounts');
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
            'code.required' => 'Voucher code is required',
            'code.unique' => 'This voucher code already exists',
            'code.max' => 'Voucher code cannot exceed 50 characters',
            'discount_amount.numeric' => 'Discount amount must be a number',
            'discount_amount.min' => 'Discount amount must be at least 0',
            'discount_percent.numeric' => 'Discount percentage must be a number',
            'discount_percent.min' => 'Discount percentage must be at least 0',
            'discount_percent.max' => 'Discount percentage cannot exceed 100',
            'min_order_amount.numeric' => 'Minimum order amount must be a number',
            'min_order_amount.min' => 'Minimum order amount must be at least 0',
            'max_discount_amount.numeric' => 'Maximum discount amount must be a number',
            'max_discount_amount.min' => 'Maximum discount amount must be at least 0',
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
            'code' => 'voucher code',
            'discount_amount' => 'discount amount',
            'discount_percent' => 'discount percentage',
            'min_order_amount' => 'minimum order amount',
            'max_discount_amount' => 'maximum discount amount',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'status' => 'status',
        ];
    }
}
