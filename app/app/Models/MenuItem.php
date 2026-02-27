<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'dish_template_id',
        'drink_template_id',
        'name',
        'description',
        'price',
        'active',
        'tags',
        'image_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'tags' => 'array',
            'price' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function dishTemplate(): BelongsTo
    {
        return $this->belongsTo(DishTemplate::class);
    }

    public function drinkTemplate(): BelongsTo
    {
        return $this->belongsTo(DrinkTemplate::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(MenuItemAudit::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://') || str_starts_with($this->image_path, '/')) {
            return $this->image_path;
        }

        if (str_starts_with($this->image_path, 'storage/')) {
            return '/'.$this->image_path;
        }

        if (str_starts_with($this->image_path, 'images/') || str_starts_with($this->image_path, 'uploads/')) {
            return '/'.$this->image_path;
        }

        return '/storage/'.$this->image_path;
    }
}
