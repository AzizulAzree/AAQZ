<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StickyNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'position_x',
        'position_y',
        'is_collapsed',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'position_x' => 'integer',
            'position_y' => 'integer',
            'is_collapsed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
