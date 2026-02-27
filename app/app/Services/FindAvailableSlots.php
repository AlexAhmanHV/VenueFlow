<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\ResourceType;
use App\Models\BlackoutDate;
use App\Models\BookingItem;
use App\Models\BookingSlotHold;
use App\Models\Restaurant;
use App\Models\Resource;
use Carbon\Carbon;

class FindAvailableSlots
{
    /**
     * @return array<int, array{resource_id:int,resource_name:string,start_time_local:string,end_time_local:string}>
     */
    public function execute(
        Restaurant $restaurant,
        ResourceType $resourceType,
        string $date,
        int $partySize,
        int $durationMinutes,
        ?string $sessionId = null
    ): array {
        if (! $restaurant->active) {
            return [];
        }

        $tz = $restaurant->timezone;
        $localDate = Carbon::parse($date, $tz)->startOfDay();
        $weekday = (int) $localDate->isoWeekday();

        $isBlackout = BlackoutDate::query()
            ->where('restaurant_id', $restaurant->id)
            ->whereDate('date', $localDate->toDateString())
            ->exists();

        if ($isBlackout) {
            return [];
        }

        $opening = $restaurant->openingHours()
            ->where('weekday', $weekday)
            ->first();

        if (! $opening) {
            return [];
        }

        $openAt = Carbon::parse($localDate->toDateString().' '.$opening->opens_at, $tz);
        $closeAt = Carbon::parse($localDate->toDateString().' '.$opening->closes_at, $tz);

        $resources = Resource::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('active', true)
            ->where('type', $resourceType->value)
            ->when($resourceType === ResourceType::TABLE, function ($query) use ($partySize) {
                $query->where('capacity_min', '<=', $partySize)
                    ->where('capacity_max', '>=', $partySize);
            })
            ->get();

        if ($resources->isEmpty()) {
            return [];
        }

        $buffer = (int) ($restaurant->setting?->default_buffer_minutes ?? 10);
        $slotInterval = (int) ($restaurant->setting?->slot_interval_minutes ?? 15);
        $slots = [];

        for ($start = $openAt->copy(); $start->lte($closeAt->copy()->subMinutes($durationMinutes)); $start->addMinutes($slotInterval)) {
            $end = $start->copy()->addMinutes($durationMinutes);
            $startUtc = $start->copy()->utc();
            $endUtc = $end->copy()->utc();

            foreach ($resources as $resource) {
                // Buffer is included in overlap check to avoid back-to-back collisions.
                $hasConflict = BookingItem::query()
                    ->join('guest_bookings', 'guest_bookings.id', '=', 'booking_items.guest_booking_id')
                    ->where('guest_bookings.status', '!=', BookingStatus::CANCELLED->value)
                    ->where('booking_items.resource_id', $resource->id)
                    ->whereNull('booking_items.deleted_at')
                    ->whereRaw(
                        "(? < (booking_items.end_time + (booking_items.buffer_after_min || ' minutes')::interval)) AND (? > (booking_items.start_time - (booking_items.buffer_before_min || ' minutes')::interval))",
                        [
                            $startUtc->toDateTimeString(),
                            $endUtc->toDateTimeString(),
                        ]
                    )
                    ->exists();

                $hasHoldConflict = BookingSlotHold::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->where('resource_id', $resource->id)
                    ->where('expires_at', '>', now('UTC'))
                    ->when($sessionId, fn ($q) => $q->where('session_id', '!=', $sessionId))
                    ->whereRaw(
                        '(? < end_time) AND (? > start_time)',
                        [
                            $startUtc->toDateTimeString(),
                            $endUtc->toDateTimeString(),
                        ]
                    )
                    ->exists();

                if (! $hasConflict && ! $hasHoldConflict) {
                    $slots[] = [
                        'resource_id' => $resource->id,
                        'resource_name' => $resource->name,
                        'start_time_local' => $start->format('Y-m-d H:i'),
                        'end_time_local' => $end->format('Y-m-d H:i'),
                    ];
                }
            }
        }

        return $slots;
    }
}
