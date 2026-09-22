<?php

namespace Tests\Feature;

use App\Models\CalendarEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_requires_an_authenticated_session(): void
    {
        $this->getJson('/api/widget/calendar')->assertUnauthorized();
    }

    public function test_calendar_reuses_shared_entries_and_follow_up_dates_without_private_fields(): void
    {
        $owner = User::factory()->create();
        $entry = CalendarEntry::factory()->create([
            'entry_date' => '2026-09-22', 'title' => 'Existing entry',
            'details' => 'Not exposed', 'source_type' => 'self', 'source_id' => $owner->id,
            'follow_up_enabled' => true, 'follow_up_days' => 2,
        ]);
        CalendarEntry::factory()->create(['entry_date' => '2025-01-01']);

        $response = $this->actingAs(User::factory()->create())->getJson('/api/widget/calendar?month=2026-09');
        $response->assertOk()->assertJsonCount(2, 'events');
        $this->assertEqualsCanonicalizing([
            ['id' => $entry->id, 'title' => 'Existing entry', 'date' => '2026-09-22'],
            ['id' => 'follow-up-'.$entry->id.'-2026-09-24', 'title' => 'Existing entry', 'date' => '2026-09-24'],
        ], $response->json('events'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_invalid_month_is_rejected_and_empty_month_is_valid_json(): void
    {
        $this->actingAs(User::factory()->create());
        $this->getJson('/api/widget/calendar?month=2026-13')->assertUnprocessable();
        $this->getJson('/api/widget/calendar?month=2026-09')->assertExactJson(['events' => []]);
    }

    public function test_widget_login_uses_existing_accounts_and_session_guard(): void
    {
        $user = User::factory()->create();
        $this->getJson('/api/widget/session')->assertOk()->assertJsonStructure(['csrf_token']);
        $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertJsonPath('authenticated', true);
        $this->assertAuthenticatedAs($user);
        $this->getJson('/api/widget/calendar')->assertOk()->assertJsonStructure(['events']);
    }

    public function test_wrong_password_does_not_grant_calendar_access(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'incorrect'])
            ->assertUnprocessable();
        $this->assertGuest();
        $this->getJson('/api/widget/calendar')->assertUnauthorized();
    }

    public function test_calendar_does_not_accept_writes(): void
    {
        $this->actingAs(User::factory()->create())->postJson('/api/widget/calendar', [])->assertStatus(405);
    }

    public function test_widget_login_retains_csrf_protection(): void
    {
        // Laravel normally bypasses CSRF for tests; exercise the real middleware.
        $this->app['env'] = 'local';
        $this->postJson('/api/widget/login', ['email' => 'nobody@example.test', 'password' => 'invalid'])
            ->assertStatus(419);
    }

    public function test_widget_login_retains_existing_login_lockout(): void
    {
        $user = User::factory()->create();
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'incorrect'])
                ->assertUnprocessable();
        }
        $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'password'])
            ->assertUnprocessable();
        $this->assertGuest();
    }
}
