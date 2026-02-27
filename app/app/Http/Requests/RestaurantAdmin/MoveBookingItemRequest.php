<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class MoveBookingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'integer'],
            'start_time_local' => ['required', 'date_format:Y-m-d H:i'],
        ];
    }
}

