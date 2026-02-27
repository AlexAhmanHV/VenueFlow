<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\BookingItem;
use App\Models\BookingNote;
use App\Models\GuestBooking;
use App\Models\Resource;
use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MoveBookingItem
{
    public function execute(
        Restaurant $restaurant,
        GuestBooking $booking,
        BookingItem $item,
        int $resourceId,
        string $startTimeLocal,
        int $actorUserId
    ): BookingItem {
        return DB::transaction(function () use ($restaurant, $booking, $item, $resourceId, $startTimeLocal, $actorUserId) {
            $lockedItem = BookingItem::query()
                ->whereKey($item->id)
                ->lockForUpdate()
                ->firstOrFail();

            $resource = Resource::query()
                ->where('restaurant_id', $restaurant->id)
                ->whereKey($resourceId)
                ->where('active', true)
                ->first();

            if (! $resource) {
                throw ValidationException::withMessages([
                    'resource_id' => 'Ogiltig resurs för restaurangen.',
                ]);
            }

            $durationMin = $lockedItem->end_time->diffInMinutes($lockedItem->start_time);
            $startUtc = Carbon::parse($startTimeLocal, $restaurant->timezone)->utc();
            $endUtc = $startUtc->copy()->addMinutes($durationMin);

            $conflict = BookingItem::query()
                ->join('guest_bookings', 'guest_bookings.id', '=', 'booking_items.guest_booking_id')
                ->where('booking_items.resource_id', $resource->id)
                ->where('booking_items.id', '!=', $lockedItem->id)
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
                    'slot' => 'Tiden är inte längre ledig för vald resurs.',
                ]);
            }

            $oldResourceId = $lockedItem->resource_id;
            $oldStart = $lockedItem->start_time->copy();
            $oldEnd = $lockedItem->end_time->copy();

            $lockedItem->update([
                'resource_id' => $resource->id,
                'start_time' => $startUtc,
                'end_time' => $endUtc,
            ]);

            BookingNote::create([
                'guest_booking_id' => $booking->id,
                'author_user_id' => $actorUserId,
                'body' => sprintf(
                    'Bokningspost flyttad: resurs %d %s-%s -> resurs %d %s-%s',
                    $oldResourceId,
                    $oldStart->timezone($restaurant->timezone)->format('Y-m-d H:i'),
                    $oldEnd->timezone($restaurant->timezone)->format('H:i'),
                    $resource->id,
                    $startUtc->timezone($restaurant->timezone)->format('Y-m-d H:i'),
                    $endUtc->timezone($restaurant->timezone)->format('H:i')
                ),
            ]);

            return $lockedItem->fresh(['resource', 'guestBooking']);
        });
    }
}

