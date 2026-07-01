<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WaitlistSlotAvailableNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Restaurant $restaurant) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('En tid har öppnat sig hos '.$this->restaurant->name)
            ->greeting('Hej '.$notifiable->customer_name.'!')
            ->line('En avbokning har skett och en tid kan ha öppnat sig på ditt önskade datum.')
            ->action('Boka nu', url('/r/'.$this->restaurant->slug))
            ->line('Platser fylls på i kö-ordning. Vi rekommenderar att du bokar snabbt.');
    }
}
