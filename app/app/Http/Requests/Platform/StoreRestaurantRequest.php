<?php

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_super_admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('restaurants', 'slug')],
            'address' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'active' => ['nullable', 'boolean'],
            'default_buffer_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'cancellation_cutoff_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'activity_counts' => ['nullable', 'array'],
            'activity_counts.GOLF' => ['nullable', 'integer', 'min:0', 'max:20'],
            'activity_counts.SHUFFLEBOARD' => ['nullable', 'integer', 'min:0', 'max:20'],
            'activity_counts.DART' => ['nullable', 'integer', 'min:0', 'max:20'],
            'activity_counts.BILLIARDS' => ['nullable', 'integer', 'min:0', 'max:20'],
            'activity_counts.TABLE' => ['nullable', 'integer', 'min:0', 'max:50'],
            'table_capacity_min' => ['nullable', 'integer', 'min:1', 'max:200'],
            'table_capacity_max' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }
}
