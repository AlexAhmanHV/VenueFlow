<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_booking_id',
        'author_user_id',
        'body',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(GuestBooking::class, 'guest_booking_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }
}
