<?php

namespace App\Models;

use App\Enums\ResourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resource extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'type',
        'name',
        'capacity_min',
        'capacity_max',
        'active',
        'position_x',
        'position_y',
    ];

    protected function casts(): array
    {
        return [
            'type' => ResourceType::class,
            'active' => 'boolean',
            'position_x' => 'float',
            'position_y' => 'float',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function isPositioned(): bool
    {
        return $this->position_x !== null && $this->position_y !== null;
    }
}
