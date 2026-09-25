<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Support\Calendar\CalendarEntryCollector;
use App\Support\Calendar\CalendarMonth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Support\SavedLogin;
use Illuminate\Support\Facades\Auth;

class WidgetCalendarController extends Controller
{
    public function session(Request $request): JsonResponse
    {
        return response()->json(['csrf_token' => $request->session()->token()])
            ->header('Cache-Control', 'private, no-store');
    }

    public function login(LoginRequest $request, SavedLogin $savedLogin): JsonResponse
    {
        // Reuse the web guard, password verification and per-user/IP lockout.
        $request->merge(['remember' => false]);
        $request->authenticate();
        $request->session()->regenerate();

        return response()->json([
            'authenticated' => true,
            'account' => $request->user()->only(['id', 'name', 'email']),
            'token' => $request->boolean('save_account') ? $savedLogin->issue($request->user()) : null,
        ])
            ->header('Cache-Control', 'private, no-store');
    }

    public function resume(Request $request, SavedLogin $savedLogin): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:150']]);
        $user = $savedLogin->userForToken($data['token']);
        abort_unless($user, 401);
        Auth::guard('web')->login($user, false);
        $request->session()->regenerate();

        return response()->json(['account' => $user->only(['id', 'name', 'email'])])
            ->header('Cache-Control', 'private, no-store');
    }

    public function forget(Request $request, SavedLogin $savedLogin): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'max:150']]);
        $savedLogin->forgetToken($data['token']);

        return response()->json(['removed' => true])->header('Cache-Control', 'private, no-store');
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['authenticated' => false])->header('Cache-Control', 'private, no-store');
    }

    public function workspace(Request $request): JsonResponse
    {
        $workspaces = $request->user()->workspaces()->with(['nodes' => fn ($query) => $query
            ->select(['id', 'workspace_id', 'parent_id', 'type', 'name', 'url', 'sort_order'])
            ->orderBy('sort_order')->orderBy('name')])->get()->map(fn ($workspace) => [
                'id' => $workspace->id, 'name' => $workspace->name,
                'nodes' => $workspace->nodes->map(fn ($node) => [
                    'id' => $node->id, 'parent_id' => $node->parent_id,
                    'type' => $node->type, 'name' => $node->name,
                    'url' => $node->isShortcut() ? $node->url : null,
                ]),
            ]);

        return response()->json([
            'workspaces' => $workspaces,
            'sticky_note' => $request->user()->stickyNote?->content,
        ])->header('Cache-Control', 'private, no-store');
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
