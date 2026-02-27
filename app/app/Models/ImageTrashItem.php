<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageTrashItem extends Model
{
    protected $fillable = [
        'token',
        'disk',
        'original_path',
        'trash_path',
        'model_type',
        'model_id',
        'column_name',
        'deleted_by_user_id',
        'expires_at',
        'restored_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'restored_at' => 'datetime',
        ];
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by_user_id');
    }
}
