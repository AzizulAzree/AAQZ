<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Support\Calendar\CalendarEntryCollector;
use App\Support\Calendar\CalendarMonth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetCalendarController extends Controller
{
    public function session(Request $request): JsonResponse
    {
        return response()->json(['csrf_token' => $request->session()->token()])
            ->header('Cache-Control', 'private, no-store');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        // Reuse the web guard, password verification and per-user/IP lockout.
        $request->merge(['remember' => false]);
        $request->authenticate();
        $request->session()->regenerate();

        return response()->json(['authenticated' => true])
            ->header('Cache-Control', 'private, no-store');
    }

    public function calendar(Request $request, CalendarEntryCollector $collector): JsonResponse
    {
        $data = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = CalendarMonth::fromMonthString($data['month'] ?? null);

        // Match the authenticated dashboard's shared-calendar visibility.
        $events = $collector->forRange($month->gridStartsAt(), $month->gridEndsAt())
            ->map(fn (array $entry) => [
                'id' => $entry['id'],
                'title' => $entry['title'],
                'date' => $entry['date']->toDateString(),
            ]);

        return response()->json(['events' => $events])
            ->header('Cache-Control', 'private, no-store');
    }
}
