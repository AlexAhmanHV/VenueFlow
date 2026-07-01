<?php

namespace App\Services;

use App\Models\GuestBooking;
use App\Models\WaitlistEntry;
use App\Notifications\WaitlistSlotAvailableNotification;

class WaitlistNotifier
{
    public function notifyNext(GuestBooking $cancelledBooking): void
    {
        $restaurant = $cancelledBooking->restaurant;

        $cancelledDate = $cancelledBooking->bookingItems()
            ->orderBy('start_time')
            ->value('start_time');

        if (! $cancelledDate) {
            return;
        }

        $preferredDate = $cancelledDate->toDateString();

        $entry = WaitlistEntry::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('preferred_date', $preferredDate)
            ->where('notified', false)
            ->orderBy('created_at')
            ->first();

        if (! $entry) {
            return;
        }

        $entry->update([
            'notified' => true,
            'notified_at' => now(),
        ]);

        $entry->notify(new WaitlistSlotAvailableNotification($restaurant));
    }
}
