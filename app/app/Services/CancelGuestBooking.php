<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\BookingStatusEvent;
use App\Models\GuestBooking;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CancelGuestBooking
{
    public function execute(Restaurant $restaurant, string $token): GuestBooking
    {
        $booking = GuestBooking::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', BookingStatus::CONFIRMED->value)
            ->orderByDesc('id')
            ->get()
            ->first(fn (GuestBooking $candidate) => Hash::check($token, $candidate->cancel_token_hash));

        if (! $booking) {
            throw ValidationException::withMessages([
                'token' => 'Ogiltig eller redan använd avbokningslänk.',
            ]);
        }

        $cutoff = $restaurant->setting?->cancellation_cutoff_minutes;
        if ($cutoff !== null) {
            $start = $booking->bookingItems()->orderBy('start_time')->first()?->start_time;
            if ($start && Carbon::now('UTC')->diffInMinutes(Carbon::parse($start), false) < $cutoff) {
                throw ValidationException::withMessages([
                    'token' => 'Det är för sent att avboka denna bokning.',
                ]);
            }
        }

        DB::transaction(function () use ($booking) {
            $from = $booking->status?->value;
            $booking->update([
                'status' => BookingStatus::CANCELLED,
                'cancelled_at' => now('UTC'),
            ]);

            // Soft-delete items so cancelled bookings no longer block DB overlap constraint.
            $booking->bookingItems()->delete();

            BookingStatusEvent::create([
                'guest_booking_id' => $booking->id,
                'from_status' => $from,
                'to_status' => BookingStatus::CANCELLED->value,
                'actor_user_id' => null,
            ]);
        });

        return $booking->refresh();
    }
}
