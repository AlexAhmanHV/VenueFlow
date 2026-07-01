<?php

namespace App\Services;

use App\Models\GuestBooking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurringBookingExpander
{
    private const MAX_OCCURRENCES = 52;

    /**
     * Given a parent booking and a recurrence rule (WEEKLY or BIWEEKLY),
     * generates up to $count child bookings shifted by the appropriate interval.
     * The caller is responsible for persisting them via CreateGuestBooking.
     *
     * @return Collection<int, array<string,mixed>>
     */
    public function expand(GuestBooking $parent, string $rule, int $count): Collection
    {
        $count = min($count, self::MAX_OCCURRENCES);
        $intervalDays = match (strtoupper($rule)) {
            'WEEKLY' => 7,
            'BIWEEKLY' => 14,
            default => throw new \InvalidArgumentException("Unsupported recurrence rule: {$rule}"),
        };

        $items = $parent->bookingItems;

        return collect(range(1, $count))->map(function (int $n) use ($parent, $items, $intervalDays) {
            $shift = $n * $intervalDays;

            return [
                'restaurant_id'    => $parent->restaurant_id,
                'customer_name'    => $parent->customer_name,
                'email'            => $parent->email,
                'phone'            => $parent->phone,
                'party_size'       => $parent->party_size,
                'note'             => $parent->note,
                'parent_booking_id' => $parent->id,
                'booking_items'    => $items->map(fn ($item) => [
                    'resource_id'     => $item->resource_id,
                    'start_time_local' => Carbon::parse($item->start_time)
                        ->addDays($shift)
                        ->format('Y-m-d H:i'),
                    'end_time_local'   => Carbon::parse($item->end_time)
                        ->addDays($shift)
                        ->format('Y-m-d H:i'),
                ])->all(),
            ];
        });
    }
}
