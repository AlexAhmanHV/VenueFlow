<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class StorePlatformResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:GOLF,SHUFFLEBOARD,DART,BILLIARDS,TABLE'],
            'name' => ['required', 'string', 'max:120'],
            'capacity_min' => ['required', 'integer', 'min:1', 'max:200'],
            'capacity_max' => ['required', 'integer', 'gte:capacity_min', 'max:200'],
            'active' => ['nullable', 'boolean'],
        ];
    }
}
