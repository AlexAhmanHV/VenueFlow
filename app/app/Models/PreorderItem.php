<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreorderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'preorder_id',
        'menu_item_id',
        'qty',
        'price_each',
    ];

    protected function casts(): array
    {
        return [
            'price_each' => 'decimal:2',
        ];
    }

    public function preorder(): BelongsTo
    {
        return $this->belongsTo(Preorder::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
