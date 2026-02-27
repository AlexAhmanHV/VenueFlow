<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Enums\StaffRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(RestaurantMembership::class);
    }

    public function membershipFor(Restaurant $restaurant): ?RestaurantMembership
    {
        return $this->memberships->firstWhere('restaurant_id', $restaurant->id)
            ?? $this->memberships()->where('restaurant_id', $restaurant->id)->first();
    }

    public function hasRestaurantRole(Restaurant $restaurant, MembershipRole $role): bool
    {
        return $this->membershipFor($restaurant)?->role === $role;
    }

    public function isManagerOrAdmin(Restaurant $restaurant): bool
    {
        $membership = $this->membershipFor($restaurant);
        if (! $membership) {
            return false;
        }

        if ($membership->role === MembershipRole::RESTAURANT_ADMIN) {
            return true;
        }

        return $membership->role === MembershipRole::STAFF && $membership->staff_role === StaffRole::MANAGER;
    }

    public function hasRestaurantAccess(Restaurant $restaurant): bool
    {
        return $this->membershipFor($restaurant) !== null || $this->is_super_admin;
    }
}
