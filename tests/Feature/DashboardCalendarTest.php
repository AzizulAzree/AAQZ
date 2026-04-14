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
        $response->assertSee('calendar-entry-details', false);
        $response->assertSee('calendar-entry-create', false);
        $response->assertSee('calendar-day-details', false);
        $response->assertSee('Project kickoff');
        $response->assertSee('Budget review');
        $response->assertSee('data-date="2026-04-10"', false);
        $response->assertDontSee('Out of month entry');
    }

    public function test_dashboard_day_modal_uses_entry_ids_for_duplicate_titles(): void
    {
        $user = User::factory()->create();

        CalendarEntry::factory()->count(3)->sequence(
            [
                'entry_date' => '2026-04-14',
                'title' => 'Calendar density check',
                'details' => 'First item',
            ],
            [
                'entry_date' => '2026-04-14',
                'title' => 'Calendar density check',
                'details' => 'Second item',
            ],
            [
                'entry_date' => '2026-04-14',
                'title' => 'Calendar density check',
                'details' => 'Third item',
            ],
        )->create();

        $response = $this
            ->actingAs($user)
            ->get('/dashboard?month=2026-04');

        $response->assertOk();
        $response->assertSee(':key="entry.id"', false);
        $response->assertSee('Calendar density check');
    }

    public function test_authenticated_user_can_add_calendar_entry_from_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/dashboard/entries', [
                'entry_date' => '2026-04-14',
                'title' => 'Doctor appointment',
                'details' => 'Bring previous lab results.',
                'month' => '2026-04',
            ]);

        $response->assertRedirect('/dashboard?month=2026-04');

        $entry = CalendarEntry::query()
            ->where('title', 'Doctor appointment')
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame('2026-04-14', $entry->entry_date->toDateString());
        $this->assertSame('Bring previous lab results.', $entry->details);
        $this->assertSame('self', $entry->source_type);
        $this->assertSame($user->id, $entry->source_id);
    }

    public function test_calendar_entry_requires_title_and_date(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from('/dashboard?month=2026-04')
            ->actingAs($user)
            ->post('/dashboard/entries', [
                'entry_date' => '',
                'title' => '',
                'month' => '2026-04',
            ]);

        $response->assertRedirect('/dashboard?month=2026-04');
        $response->assertSessionHasErrors(['entry_date', 'title']);
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
