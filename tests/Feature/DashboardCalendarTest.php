<?php

namespace Tests\Feature;

use App\Models\CalendarEntry;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_selected_month_and_entry_summaries(): void
    {
        $user = User::factory()->create(['color' => '#3B82F6']);

        CalendarEntry::create([
            'entry_date' => '2026-04-10',
            'title' => 'Project kickoff',
            'details' => 'Initial planning session',
            'source_type' => 'self',
            'source_id' => $user->id,
        ]);

        CalendarEntry::create([
            'entry_date' => '2026-04-10',
            'title' => 'Budget review',
            'source_type' => 'self',
            'source_id' => $user->id,
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
        $response->assertSee('background-color: #3B82F6', false);
        $response->assertSee('data-date="2026-04-10"', false);
        $response->assertDontSee('Out of month entry');
        $response->assertSee('Initial planning session');
        $response->assertSee('Created');
        $response->assertSee('Updated');
        $response->assertSee('created_at', false);
        $response->assertSee('updated_at', false);
        $response->assertDontSee('Added here');
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
        $this->assertFalse($entry->follow_up_enabled);
        $this->assertNull($entry->follow_up_days);
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

    public function test_dashboard_shows_a_three_day_reminder_summary(): void
    {
        CarbonImmutable::setTestNow('2026-04-14 09:00:00');

        try {
            $user = User::factory()->create(['color' => '#3B82F6']);

            CalendarEntry::create([
                'entry_date' => '2026-04-14',
                'title' => 'Morning check-in',
                'details' => 'Bring notes for the day.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            CalendarEntry::create([
                'entry_date' => '2026-04-15',
                'title' => 'Dentist visit',
                'details' => 'Appointment at 3 PM.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            CalendarEntry::create([
                'entry_date' => '2026-04-16',
                'title' => 'Family dinner',
                'details' => 'Reservation confirmed.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            CalendarEntry::create([
                'entry_date' => '2026-04-18',
                'title' => 'Later reminder',
            ]);

            $response = $this
                ->actingAs($user)
                ->get('/dashboard?month=2026-04');

            $response->assertOk();
            $response->assertSee('Reminder');
            $response->assertSee('The next few days at a glance');
            $response->assertSee('Today');
            $response->assertSee('Tomorrow');
            $response->assertSee('Thursday');
            $response->assertSee('Morning check-in');
            $response->assertSee('Dentist visit');
            $response->assertSee('Family dinner');
            $response->assertSee('14 Apr');
            $response->assertSee('15 Apr');
            $response->assertSee('16 Apr');
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_dashboard_collapses_empty_weekend_reminders_into_one_card(): void
    {
        CarbonImmutable::setTestNow('2026-04-17 09:00:00');

        try {
            $user = User::factory()->create(['color' => '#3B82F6']);

            CalendarEntry::create([
                'entry_date' => '2026-04-17',
                'title' => 'Friday handoff',
                'details' => 'Wrap up the final review.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            $response = $this
                ->actingAs($user)
                ->get('/dashboard?month=2026-04');

            $response->assertOk();
            $response->assertSee('Today');
            $response->assertSee('Weekend');
            $response->assertSee('Fri, 17 Apr');
            $response->assertSee('Sat, 18 Apr & Sun, 19 Apr');
            $response->assertSee('Nothing lined up here.');
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_dashboard_labels_a_single_empty_weekend_day_as_weekend(): void
    {
        CarbonImmutable::setTestNow('2026-04-16 09:00:00');

        try {
            $user = User::factory()->create(['color' => '#3B82F6']);

            CalendarEntry::create([
                'entry_date' => '2026-04-16',
                'title' => 'Thursday review',
                'details' => 'Check the week before wrap-up.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            CalendarEntry::create([
                'entry_date' => '2026-04-17',
                'title' => 'Friday planning',
                'details' => 'Lock Monday priorities.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            $response = $this
                ->actingAs($user)
                ->get('/dashboard?month=2026-04');

            $response->assertOk();
            $response->assertSee('Weekend');
            $response->assertSee('Sat, 18 Apr & Sun, 19 Apr');
            $response->assertSee('Nothing lined up here.');
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_dashboard_weekend_card_includes_entries_from_sunday_even_when_strip_ends_on_saturday(): void
    {
        CarbonImmutable::setTestNow('2026-04-16 09:00:00');

        try {
            $user = User::factory()->create(['color' => '#3B82F6']);

            CalendarEntry::create([
                'entry_date' => '2026-04-16',
                'title' => 'Thursday review',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            CalendarEntry::create([
                'entry_date' => '2026-04-17',
                'title' => 'Friday planning',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            CalendarEntry::create([
                'entry_date' => '2026-04-19',
                'title' => 'Sunday catch-up',
                'details' => 'Prep for Monday.',
                'source_type' => 'self',
                'source_id' => $user->id,
            ]);

            $response = $this
                ->actingAs($user)
                ->get('/dashboard?month=2026-04');

            $response->assertOk();
            $response->assertSee('Weekend');
            $response->assertSee('Sat, 18 Apr & Sun, 19 Apr');
            $response->assertSee('Sunday catch-up');
            $response->assertSee('Prep for Monday.');
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_authenticated_user_can_add_calendar_entry_with_follow_up_settings(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/dashboard/entries', [
                'entry_date' => '2026-04-14',
                'title' => 'Client recap',
                'details' => 'Send the summary and next steps.',
                'follow_up_enabled' => '1',
                'follow_up_days' => '4',
                'month' => '2026-04',
            ]);

        $response->assertRedirect('/dashboard?month=2026-04');

        $entry = CalendarEntry::query()
            ->where('title', 'Client recap')
            ->first();

        $this->assertNotNull($entry);
        $this->assertTrue($entry->follow_up_enabled);
        $this->assertSame(4, $entry->follow_up_days);
    }

    public function test_follow_up_days_are_required_when_follow_up_is_enabled(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->from('/dashboard?month=2026-04')
            ->actingAs($user)
            ->post('/dashboard/entries', [
                'entry_date' => '2026-04-14',
                'title' => 'Missing follow-up day value',
                'follow_up_enabled' => '1',
                'month' => '2026-04',
            ]);

        $response->assertRedirect('/dashboard?month=2026-04');
        $response->assertSessionHasErrors(['follow_up_days']);
    }

    public function test_follow_up_entries_appear_on_the_future_calendar_day_with_tag(): void
    {
        $user = User::factory()->create(['color' => '#3B82F6']);

        CalendarEntry::create([
            'entry_date' => '2026-04-10',
            'title' => 'Invoice review',
            'details' => 'Need to check for client reply.',
            'follow_up_enabled' => true,
            'follow_up_days' => 2,
            'source_type' => 'self',
            'source_id' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/dashboard?month=2026-04');

        $response->assertOk();
        $response->assertSee('Invoice review');
        $response->assertSee('Follow Up');
    }
}
