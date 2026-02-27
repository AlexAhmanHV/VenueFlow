<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class SaveDishTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'tags' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,svg', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'crop_x' => ['nullable', 'integer', 'min:0'],
            'crop_y' => ['nullable', 'integer', 'min:0'],
            'crop_w' => ['nullable', 'integer', 'min:1'],
            'crop_h' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
