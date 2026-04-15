<?php

namespace App\Models;

use Database\Factories\CalendarEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEntry extends Model
{
    /** @use HasFactory<CalendarEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'title',
        'details',
        'follow_up_enabled',
        'follow_up_days',
        'source_type',
        'source_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'immutable_date',
            'follow_up_enabled' => 'boolean',
            'follow_up_days' => 'integer',
            'source_id' => 'integer',
        ];
    }
}
