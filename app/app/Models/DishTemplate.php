<?php

namespace App\Models;

use App\Support\MenuPhoto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DishTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'base_price',
        'active',
        'tags',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'active' => 'boolean',
            'tags' => 'array',
        ];
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path || MenuPhoto::isGeneratedPlaceholder($this->image_path)) {
            $photo = MenuPhoto::urlFor($this->name);
            if ($photo !== null) {
                return $photo;
            }
        }

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
