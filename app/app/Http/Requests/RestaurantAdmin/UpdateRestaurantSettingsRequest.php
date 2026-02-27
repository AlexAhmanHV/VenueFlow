<?php

namespace App\Http\Requests\RestaurantAdmin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'default_buffer_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'cancellation_cutoff_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'slot_interval_minutes' => ['required', 'integer', 'in:5,10,15,20,30,60'],
            'max_simultaneous_bookings' => ['nullable', 'integer', 'min:1', 'max:500'],
            'default_durations.GOLF' => ['required', 'integer', 'min:15', 'max:300'],
            'default_durations.SHUFFLEBOARD' => ['required', 'integer', 'min:15', 'max:300'],
            'default_durations.DART' => ['required', 'integer', 'min:15', 'max:300'],
            'default_durations.BILLIARDS' => ['required', 'integer', 'min:15', 'max:300'],
            'default_durations.TABLE' => ['required', 'integer', 'min:15', 'max:300'],
        ];
    }
}
