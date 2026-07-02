<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'finance_period_id',
    'finance_commitment_category_id',
    'record_type',
    'recorded_on',
    'amount',
    'title',
    'notes',
])]
class FinanceRecord extends Model
{
    protected function casts(): array
    {
        return [
            'recorded_on' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financePeriod(): BelongsTo
    {
        return $this->belongsTo(FinancePeriod::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinanceCommitmentCategory::class, 'finance_commitment_category_id');
    }
}
