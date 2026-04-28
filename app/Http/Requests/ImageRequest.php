<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'images' => 'required',
            'images.*' => 'file|mimes:jpg,jpeg,png|max:2048'
        ];
    }
    public function messages(): array
    {
        return [
            'images.required' => 'Vui lòng chọn ảnh',
            'images.*.file' => 'Ảnh phải là file',
            'images.*.mimes' => 'Ảnh phải là file jpg, jpeg, png',
            'images.*.max' => 'Ảnh không được vượt quá 2MB',
        ];
    }
}
