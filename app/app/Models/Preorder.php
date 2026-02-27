<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Preorder extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_booking_id',
        'serve_time',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'serve_time' => 'datetime',
        ];
    }

    public function guestBooking(): BelongsTo
    {
        return $this->belongsTo(GuestBooking::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PreorderItem::class);
    }
}
