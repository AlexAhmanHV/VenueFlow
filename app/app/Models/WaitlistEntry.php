<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class WaitlistEntry extends Model
{
    use Notifiable;

    protected $fillable = [
        'restaurant_id',
        'customer_name',
        'email',
        'phone',
        'party_size',
        'preferred_date',
        'preferred_time',
        'note',
        'notified',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'notified' => 'boolean',
            'notified_at' => 'datetime',
            'preferred_date' => 'date',
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
}
