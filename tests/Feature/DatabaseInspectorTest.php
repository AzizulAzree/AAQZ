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
        $response->assertSee('Tables');
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
        $response->assertSee('Field details');
        $response->assertSee('entry_date');
        $response->assertSee('title');
        $response->assertSee('Saved records');
        $response->assertSee('calendar_entries');
        $response->assertSee('A very long calendar entry title');
    }

    public function test_admin_can_delete_a_record_from_the_data_browser(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $entry = CalendarEntry::create([
            'entry_date' => '2026-04-14',
            'title' => 'Remove me',
            'details' => 'This should be deleted from the browser.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->delete('/admin/database/calendar_entries', [
                'record_key' => $entry->id,
                'page' => 1,
            ]);

        $response->assertRedirect('/admin/database/calendar_entries?page=1');
        $response->assertSessionHas('status', 'record-deleted');

        $this->assertDatabaseMissing('calendar_entries', [
            'id' => $entry->id,
        ]);
    }

    public function test_admin_can_update_a_record_from_the_data_browser(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        $entry = CalendarEntry::create([
            'entry_date' => '2026-04-14',
            'title' => 'Original title',
            'details' => 'Original details',
        ]);

        $response = $this
            ->actingAs($admin)
            ->put('/admin/database/calendar_entries', [
                'record_key' => $entry->id,
                'page' => 1,
                'values' => [
                    'entry_date' => '2026-04-18',
                    'title' => 'Updated title',
                    'details' => 'Updated details',
                    'follow_up_enabled' => '1',
                    'follow_up_days' => '3',
                    'source_type' => 'calendar_entry',
                    'source_id' => '12',
                ],
            ]);

        $response->assertRedirect('/admin/database/calendar_entries?page=1');
        $response->assertSessionHas('status', 'record-updated');

        $this->assertDatabaseHas('calendar_entries', [
            'id' => $entry->id,
            'title' => 'Updated title',
            'details' => 'Updated details',
            'follow_up_days' => 3,
        ]);
    }

    public function test_non_admin_users_cannot_delete_a_record_from_the_data_browser(): void
    {
        User::factory()->create([
            'email' => 'first@example.com',
        ]);

        $nonAdmin = User::factory()->create([
            'email' => 'second@example.com',
        ]);

        $entry = CalendarEntry::create([
            'entry_date' => '2026-04-14',
            'title' => 'Keep me',
        ]);

        $response = $this
            ->actingAs($nonAdmin)
            ->delete('/admin/database/calendar_entries', [
                'record_key' => $entry->id,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('calendar_entries', [
            'id' => $entry->id,
        ]);
    }

    public function test_non_admin_users_cannot_update_a_record_from_the_data_browser(): void
    {
        User::factory()->create([
            'email' => 'first@example.com',
        ]);

        $nonAdmin = User::factory()->create([
            'email' => 'second@example.com',
        ]);

        $entry = CalendarEntry::create([
            'entry_date' => '2026-04-14',
            'title' => 'Keep title',
        ]);

        $response = $this
            ->actingAs($nonAdmin)
            ->put('/admin/database/calendar_entries', [
                'record_key' => $entry->id,
                'values' => [
                    'title' => 'Should not update',
                ],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('calendar_entries', [
            'id' => $entry->id,
            'title' => 'Keep title',
        ]);
    }
}
