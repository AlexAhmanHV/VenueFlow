<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'guest_booking_id',
        'resource_id',
        'start_time',
        'end_time',
        'buffer_before_min',
        'buffer_after_min',
        'price_minor',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function guestBooking(): BelongsTo
    {
        return $this->belongsTo(GuestBooking::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }
}
