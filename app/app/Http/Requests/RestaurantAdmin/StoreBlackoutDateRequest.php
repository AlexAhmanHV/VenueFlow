<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlackoutDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
