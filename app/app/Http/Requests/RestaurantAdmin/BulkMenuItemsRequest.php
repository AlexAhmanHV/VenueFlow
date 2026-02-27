<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkMenuItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['integer', 'min:1'],
            'action' => ['required', 'string', Rule::in([
                'set_active',
                'set_inactive',
                'delete',
                'remove_image',
                'add_tag',
                'remove_tag',
                'increase_price_pct',
                'decrease_price_pct',
            ])],
            'tag' => ['nullable', 'string', 'max:50'],
            'percentage' => ['nullable', 'numeric', 'min:1', 'max:90'],
        ];
    }
}
