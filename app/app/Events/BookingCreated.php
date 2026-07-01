<?php

namespace App\Events;

use App\Models\GuestBooking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly GuestBooking $booking) {}

    public function broadcastOn(): array
    {
        return [new Channel("restaurant.{$this->booking->restaurant_id}.bookings")];
    }

    public function broadcastWith(): array
    {
        return [
            'id'            => $this->booking->id,
            'public_id'     => $this->booking->public_id,
            'customer_name' => $this->booking->customer_name,
            'party_size'    => $this->booking->party_size,
            'status'        => $this->booking->status->value,
            'created_at'    => $this->booking->created_at?->toIso8601String(),
        ];
    }
}
