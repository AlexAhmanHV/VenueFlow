<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'address',
        'timezone',
        'phone',
        'email',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function setting(): HasOne
    {
        return $this->hasOne(RestaurantSetting::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(RestaurantMembership::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(OpeningHour::class);
    }

    public function blackoutDates(): HasMany
    {
        return $this->hasMany(BlackoutDate::class);
    }

    public function guestBookings(): HasMany
    {
        return $this->hasMany(GuestBooking::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}
