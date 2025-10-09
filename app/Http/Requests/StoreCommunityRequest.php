<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommunityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Any authenticated user can create a community
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'profile_image_url' => 'nullable|string|max:255|url',
            'is_private' => 'boolean',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Community name is required.',
            'name.max' => 'Community name cannot exceed 150 characters.',
            'profile_image_url.url' => 'Profile image URL must be a valid URL.',
            'profile_image_url.max' => 'Profile image URL cannot exceed 255 characters.',
            'is_private.boolean' => 'Privacy setting must be true or false.',
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'community name',
            'description' => 'community description',
            'profile_image_url' => 'profile image URL',
            'is_private' => 'privacy setting',
        ];
    }
}
