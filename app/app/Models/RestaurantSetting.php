<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'default_buffer_minutes',
        'slot_interval_minutes',
        'max_simultaneous_bookings',
        'default_durations',
        'cancellation_cutoff_minutes',
    ];

    protected function casts(): array
    {
        return [
            'default_durations' => 'array',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
