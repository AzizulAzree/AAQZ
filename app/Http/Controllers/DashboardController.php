<?php

namespace App\Http\Controllers;

use App\Support\Calendar\CalendarEntryCollector;
use App\Support\Calendar\CalendarMonth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, CalendarEntryCollector $entryCollector): View
    {
        $calendar = CalendarMonth::fromMonthString($request->string('month')->toString());

        return view('dashboard', [
            'calendar' => $calendar->withEntries(
                $entryCollector->forRange($calendar->gridStartsAt(), $calendar->gridEndsAt()),
            ),
        ]);
    }
}
