<?php

namespace App\Services;

use App\Enums\BookingStatus;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ResourceOccupancyResolver
{
    /**
     * @param  Collection  $bookings  GuestBooking models with bookingItems.resource eager-loaded.
     * @return array<int, string> resource_id => 'occupied'|'reserved_soon'. Resources with
     *                            neither state are simply absent from the map (treat as free).
     */
    public static function resolve(Collection $bookings, CarbonInterface $now): array
    {
        $reservedSoonCutoff = $now->copy()->addMinutes(30);
        $statuses = [];

        foreach ($bookings as $booking) {
            if (in_array($booking->status, [BookingStatus::CANCELLED, BookingStatus::NO_SHOW], true)) {
                continue;
            }

            foreach ($booking->bookingItems as $item) {
                $resourceId = $item->resource_id;

                if ($item->start_time->lte($now) && $item->end_time->gte($now)) {
                    $statuses[$resourceId] = 'occupied';
                    continue;
                }

                if (($statuses[$resourceId] ?? null) === 'occupied') {
                    continue;
                }

                if ($item->start_time->gt($now) && $item->start_time->lte($reservedSoonCutoff)) {
                    $statuses[$resourceId] = 'reserved_soon';
                }
            }
        }

        return $statuses;
    }
}
