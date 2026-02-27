<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class GuestBooking extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'restaurant_id',
        'public_id',
        'status',
        'customer_name',
        'email',
        'phone',
        'party_size',
        'note',
        'cancel_token_hash',
        'cancelled_at',
    ];

    protected $hidden = [
        'cancel_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function preorders(): HasMany
    {
        return $this->hasMany(Preorder::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(BookingStatusEvent::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BookingNote::class, 'guest_booking_id');
    }
}
