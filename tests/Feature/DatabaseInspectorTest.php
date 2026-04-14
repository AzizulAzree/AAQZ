<?php

namespace Tests\Feature;

use App\Models\CalendarEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseInspectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_overview_requires_admin_access(): void
    {
        $response = $this->get('/admin/database');

        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_open_database_overview(): void
    {
        User::factory()->create([
            'email' => 'first@example.com',
        ]);

        $nonAdmin = User::factory()->create([
            'email' => 'second@example.com',
        ]);

        $response = $this
            ->actingAs($nonAdmin)
            ->get('/admin/database');

        $response->assertForbidden();
    }

    public function test_admin_can_view_database_overview(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        CalendarEntry::factory()->count(2)->create();

        $response = $this
            ->actingAs($admin)
            ->get('/admin/database');

        $response->assertOk();
        $response->assertSee('Database Overview');
        $response->assertSee('users');
        $response->assertSee('calendar_entries');
    }

    public function test_admin_can_view_table_details_and_row_preview(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        CalendarEntry::create([
            'entry_date' => '2026-04-14',
            'title' => 'A very long calendar entry title that should still be readable in preview mode',
            'details' => 'Long details for inspector preview',
        ]);

        $response = $this
            ->actingAs($admin)
            ->get('/admin/database/calendar_entries');

        $response->assertOk();
        $response->assertSee('Columns');
        $response->assertSee('entry_date');
        $response->assertSee('title');
        $response->assertSee('Latest Rows');
        $response->assertSee('calendar_entries');
        $response->assertSee('A very long calendar entry title');
    }
}
