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
        'source_type',
        'source_id',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'immutable_date',
            'source_id' => 'integer',
        ];
    }
}
