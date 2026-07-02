<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'period_year',
    'period_month',
    'salary_received_on',
    'salary_amount',
    'carry_balance_before_salary',
    'remarks',
])]
class FinancePeriod extends Model
{
    protected function casts(): array
    {
        return [
            'salary_received_on' => 'date',
            'salary_amount' => 'decimal:2',
            'carry_balance_before_salary' => 'decimal:2',
            'period_year' => 'integer',
            'period_month' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commitments(): HasMany
    {
        return $this->hasMany(FinancePeriodCommitment::class)
            ->orderBy('status')
            ->orderBy('name_snapshot');
    }

    public function records(): HasMany
    {
        return $this->hasMany(FinanceRecord::class)->latest('recorded_on');
    }
}
