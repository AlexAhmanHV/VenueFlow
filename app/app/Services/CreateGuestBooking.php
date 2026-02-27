<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\BookingItem;
use App\Models\BookingStatusEvent;
use App\Models\GuestBooking;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Notifications\GuestBookingConfirmedNotification;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateGuestBooking
{
    /**
     * @param  array<string,mixed>  $payload
     */
    public function execute(Restaurant $restaurant, array $payload): GuestBooking
    {
        $tz = $restaurant->timezone;
        $buffer = (int) ($restaurant->setting?->default_buffer_minutes ?? 10);
        $maxSimultaneous = $restaurant->setting?->max_simultaneous_bookings;
        $cancelToken = Str::random(64);

        $bookingItems = $payload['booking_items'] ?? [];
        if (! is_array($bookingItems) || empty($bookingItems)) {
            throw ValidationException::withMessages([
                'booking_items' => 'Du måste välja minst en aktivitet innan bokningen kan slutföras.',
            ]);
        }

        try {
            $booking = DB::transaction(function () use ($restaurant, $payload, $tz, $buffer, $cancelToken, $bookingItems) {
                $booking = GuestBooking::create([
                    'restaurant_id' => $restaurant->id,
                    'public_id' => (string) Str::uuid(),
                    'status' => BookingStatus::CONFIRMED,
                    'customer_name' => $payload['customer_name'],
                    'email' => $payload['email'],
                    'phone' => $payload['phone'] ?? null,
                    'party_size' => (int) $payload['party_size'],
                    'note' => $payload['note'] ?? null,
                    'cancel_token_hash' => Hash::make($cancelToken),
                ]);

                foreach ($bookingItems as $item) {
                    $startUtc = Carbon::parse($item['start_time_local'], $tz)->utc();
                    $endUtc = Carbon::parse($item['end_time_local'], $tz)->utc();

                    $resource = $restaurant->resources()
                        ->whereKey($item['resource_id'])
                        ->where('active', true)
                        ->first();

                    if (! $resource) {
                        throw ValidationException::withMessages(['resource_id' => 'Ogiltig resurs för vald restaurang.']);
                    }

                    // Lock matching rows before checking overlap to prevent concurrent double-bookings.
                    $conflict = BookingItem::query()
                        ->join('guest_bookings', 'guest_bookings.id', '=', 'booking_items.guest_booking_id')
                        ->where('booking_items.resource_id', $resource->id)
                        ->where('guest_bookings.status', '!=', BookingStatus::CANCELLED->value)
                        ->whereNull('booking_items.deleted_at')
                        ->whereRaw(
                            "(? < (booking_items.end_time + (booking_items.buffer_after_min || ' minutes')::interval)) AND (? > (booking_items.start_time - (booking_items.buffer_before_min || ' minutes')::interval))",
                            [
                                $startUtc->toDateTimeString(),
                                $endUtc->toDateTimeString(),
                            ]
                        )
                        ->lockForUpdate()
                        ->exists();

                    if ($conflict) {
                        throw ValidationException::withMessages([
                            'slot' => 'En vald tid är inte längre tillgänglig. Välj en annan tid.',
                        ]);
                    }

                    if ($maxSimultaneous !== null) {
                        $simultaneousCount = BookingItem::query()
                            ->join('guest_bookings', 'guest_bookings.id', '=', 'booking_items.guest_booking_id')
                            ->where('guest_bookings.restaurant_id', $restaurant->id)
                            ->where('guest_bookings.status', '!=', BookingStatus::CANCELLED->value)
                            ->whereNull('booking_items.deleted_at')
                            ->whereRaw(
                                '(? < booking_items.end_time) AND (? > booking_items.start_time)',
                                [
                                    $startUtc->toDateTimeString(),
                                    $endUtc->toDateTimeString(),
                                ]
                            )
                            ->distinct('booking_items.guest_booking_id')
                            ->count('booking_items.guest_booking_id');

                        if ($simultaneousCount >= $maxSimultaneous) {
                            throw ValidationException::withMessages([
                                'slot' => 'Max antal samtidiga bokningar är uppnått för den valda tiden.',
                            ]);
                        }
                    }

                    $booking->bookingItems()->create([
                        'resource_id' => $resource->id,
                        'start_time' => $startUtc,
                        'end_time' => $endUtc,
                        'buffer_before_min' => $buffer,
                        'buffer_after_min' => $buffer,
                    ]);
                }

                if (! empty($payload['preorder_items']) && is_array($payload['preorder_items'])) {
                    $serveTime = ! empty($payload['preorder_serve_time_local'])
                        ? Carbon::parse($payload['preorder_serve_time_local'], $tz)->utc()
                        : null;

                    $preorder = $booking->preorders()->create([
                        'serve_time' => $serveTime,
                        'note' => $payload['preorder_note'] ?? null,
                    ]);

                    foreach ($payload['preorder_items'] as $itemId => $qty) {
                        if ((int) $qty <= 0) {
                            continue;
                        }

                        $menuItem = MenuItem::query()
                            ->where('restaurant_id', $restaurant->id)
                            ->whereKey((int) $itemId)
                            ->where('active', true)
                            ->first();

                        if (! $menuItem) {
                            continue;
                        }

                        $preorder->items()->create([
                            'menu_item_id' => $menuItem->id,
                            'qty' => (int) $qty,
                            'price_each' => $menuItem->price,
                        ]);
                    }
                }

                BookingStatusEvent::create([
                    'guest_booking_id' => $booking->id,
                    'from_status' => null,
                    'to_status' => BookingStatus::CONFIRMED->value,
                    'actor_user_id' => null,
                ]);

                return $booking->load(['bookingItems.resource', 'restaurant']);
            });
        } catch (QueryException $e) {
            // PostgreSQL exclusion constraint violation (23P01) is translated to a user-facing slot conflict.
            if (($e->errorInfo[0] ?? null) === '23P01') {
                throw ValidationException::withMessages([
                    'slot' => 'En vald tid är inte längre tillgänglig. Välj en annan tid.',
                ]);
            }

            throw $e;
        }

        $booking->notify(new GuestBookingConfirmedNotification($cancelToken));

        return $booking;
    }
}
