<?php

namespace App\Notifications;

use App\Models\GuestBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GuestBookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $cancelToken)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var GuestBooking $booking */
        $booking = $notifiable;
        $slug = $booking->restaurant->slug;

        return (new MailMessage)
            ->subject('Bokningsbekräftelse #'.$booking->public_id)
            ->greeting('Hej '.$booking->customer_name.'!')
            ->line('Din bokning är bekräftad.')
            ->action('Visa bokning', url("/r/{$slug}/booking/{$booking->public_id}"))
            ->action('Avboka bokning', url("/r/{$slug}/cancel?token={$this->cancelToken}"))
            ->line('Tack för din bokning.');
    }
}
