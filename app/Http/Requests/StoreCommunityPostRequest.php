<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization will be handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'caption' => 'nullable|string',
            'images' => 'nullable|array|max:10', // Maximum 10 images
            'images.*' => 'string|url|max:255', // Each image must be a valid URL
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'images.array' => 'Images must be provided as an array.',
            'images.max' => 'You can upload a maximum of 10 images.',
            'images.*.url' => 'Each image must be a valid URL.',
            'images.*.max' => 'Each image URL cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'caption' => 'post caption',
            'images' => 'post images',
            'images.*' => 'image URL',
        ];
    }
}
