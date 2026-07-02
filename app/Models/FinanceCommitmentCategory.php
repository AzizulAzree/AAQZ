<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'default_amount',
    'color',
    'icon',
    'is_active',
    'sort_order',
])]
class FinanceCommitmentCategory extends Model
{
    protected function casts(): array
    {
        return [
            'default_amount' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function periodCommitments(): HasMany
    {
        return $this->hasMany(FinancePeriodCommitment::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(FinanceRecord::class);
    }
}
