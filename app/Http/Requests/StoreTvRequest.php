<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTvRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_name' => 'required|string|max:100',
            'category_description' => 'nullable|string',
            'category_status' => 'sometimes|in:active,inactive',
            'title' => 'required|string|max:200',
            'link' => 'required|url',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
            'status' => 'sometimes|in:active,inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_name.required' => 'Nama kategori TV wajib diisi.',
            'category_name.max' => 'Nama kategori TV maksimal 100 karakter.',
            'title.required' => 'Judul TV wajib diisi.',
            'title.max' => 'Judul TV maksimal 200 karakter.',
            'link.required' => 'Link TV wajib diisi.',
            'link.url' => 'Link TV harus berupa URL yang valid.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Gambar harus berformat: jpeg, png, jpg, gif.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}