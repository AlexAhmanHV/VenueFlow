<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
