<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'party_size' => ['required', 'integer', 'min:1', 'max:50'],
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'note' => ['nullable', 'string', 'max:2000'],
            'booking_items' => ['nullable', 'array', 'min:1'],
            'booking_items.*.resource_id' => ['required_with:booking_items', 'integer'],
            'booking_items.*.start_time_local' => ['required_with:booking_items', 'date'],
            'booking_items.*.end_time_local' => ['required_with:booking_items', 'date'],
            'preorder_items' => ['nullable', 'array'],
            'preorder_items.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'preorder_serve_time_local' => ['nullable', 'date'],
            'preorder_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
