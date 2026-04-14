<?php

namespace Tests\Feature;

use App\Models\CalendarEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_selected_month_and_entry_summaries(): void
    {
        $user = User::factory()->create();

        CalendarEntry::create([
            'entry_date' => '2026-04-10',
            'title' => 'Project kickoff',
            'details' => 'Initial planning session',
        ]);

        CalendarEntry::create([
            'entry_date' => '2026-04-10',
            'title' => 'Budget review',
        ]);

        CalendarEntry::create([
            'entry_date' => '2026-05-02',
            'title' => 'Out of month entry',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard?month=2026-04');

        $response->assertOk();
        $response->assertSee('April 2026');
        $response->assertSee('data-calendar-grid', false);
        $response->assertSee('data-calendar-week="1"', false);
        $response->assertSee('data-calendar-week="5"', false);
        $response->assertSee('data-date="2026-03-29"', false);
        $response->assertSee('data-date="2026-05-02"', false);
        $response->assertSee('Project kickoff');
        $response->assertSee('Budget review');
        $response->assertSee('data-date="2026-04-10"', false);
        $response->assertDontSee('Out of month entry');
    }

    public function test_invalid_month_query_falls_back_to_the_current_month(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard?month=not-a-month');

        $response->assertOk();
        $response->assertSee(now()->startOfMonth()->format('F Y'));
    }
}
