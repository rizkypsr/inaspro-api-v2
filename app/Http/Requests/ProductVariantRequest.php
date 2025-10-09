<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductVariantRequest extends FormRequest
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
        $rules = [
            'product_id' => 'required|exists:products,id',
            'variant_name' => 'required|string|max:150',
            'image_url' => 'nullable|string|url',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ];

        // For updates, ignore the current record when checking SKU uniqueness
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['sku'] = [
                'required',
                'string',
                Rule::unique('product_variants')->ignore($this->route('product_variant'))
            ];
        } else {
            $rules['sku'] = 'required|string|unique:product_variants,sku';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'SKU is required.',
            'sku.string' => 'SKU must be a string.',
            'sku.unique' => 'SKU must be unique.',
            'product_id.required' => 'Product is required.',
            'product_id.exists' => 'Selected product does not exist.',
            'variant_name.required' => 'Variant name is required.',
            'variant_name.string' => 'Variant name must be a string.',
            'variant_name.max' => 'Variant name cannot exceed 150 characters.',
            'image_url.string' => 'Image URL must be a string.',
            'image_url.url' => 'Image URL must be a valid URL.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a number.',
            'price.min' => 'Price must be at least 0.',
            'stock.required' => 'Stock is required.',
            'stock.integer' => 'Stock must be an integer.',
            'stock.min' => 'Stock must be at least 0.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }
}
