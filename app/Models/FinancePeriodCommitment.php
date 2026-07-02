<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'finance_period_id',
    'finance_commitment_category_id',
    'name_snapshot',
    'amount',
    'status',
    'paid_on',
    'notes',
])]
class FinancePeriodCommitment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_on' => 'date',
        ];
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
