<?php

namespace Tests\Feature;

use App\Models\CalendarEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_creation_returns_selected_date_and_stores_follow_up(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('dashboard.entries.store'), [
            'entry_date' => '2026-09-15', 'title' => 'Review', 'details' => 'Check notes', 'follow_up_enabled' => true, 'follow_up_days' => 3,
        ])->assertOk()->assertJsonPath('redirect', route('dashboard', ['month' => '2026-09', 'date' => '2026-09-15']));
        $this->assertDatabaseHas('calendar_entries', ['title' => 'Review', 'source_id' => $user->id, 'follow_up_days' => 3]);
    }

    public function test_owner_can_update_date_and_remove_follow_up(): void
    {
        $user = User::factory()->create();
        $entry = CalendarEntry::factory()->create(['source_type' => 'self', 'source_id' => $user->id, 'follow_up_enabled' => true, 'follow_up_days' => 3]);
        $this->actingAs($user)->patchJson(route('dashboard.entries.update', $entry), [
            'entry_date' => '2026-10-02', 'title' => 'Rescheduled', 'details' => 'New notes', 'follow_up_enabled' => false, 'follow_up_days' => 5,
        ])->assertOk()->assertJsonPath('redirect', route('dashboard', ['month' => '2026-10', 'date' => '2026-10-02']));
        $entry->refresh();
        $this->assertSame('Rescheduled', $entry->title);
        $this->assertSame('New notes', $entry->details);
        $this->assertSame('2026-10-02', $entry->entry_date->toDateString());
        $this->assertNull($entry->follow_up_days);
    }

    public function test_other_users_cannot_modify_shared_entries(): void
    {
        $owner = User::factory()->create();
        $entry = CalendarEntry::factory()->create(['source_type' => 'self', 'source_id' => $owner->id]);
        $this->actingAs(User::factory()->create())->patchJson(route('dashboard.entries.update', $entry), [])->assertForbidden();
        $this->deleteJson(route('dashboard.entries.destroy', $entry), ['confirmed' => true])->assertForbidden();
        $this->assertModelExists($entry);
    }

    public function test_edit_validation_preserves_entry(): void
    {
        $user = User::factory()->create();
        $entry = CalendarEntry::factory()->create(['source_type' => 'self', 'source_id' => $user->id, 'title' => 'Original']);
        $this->actingAs($user)->patchJson(route('dashboard.entries.update', $entry), [
            'title' => '', 'entry_date' => 'invalid', 'follow_up_enabled' => true, 'follow_up_days' => 31,
        ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'entry_date', 'follow_up_days']);
        $this->assertSame('Original', $entry->fresh()->title);
    }

    public function test_confirmed_deletion_removes_entry_and_generated_follow_up(): void
    {
        $user = User::factory()->create();
        $entry = CalendarEntry::factory()->create(['title' => 'Delete test', 'entry_date' => '2026-09-15', 'source_type' => 'self', 'source_id' => $user->id, 'follow_up_enabled' => true, 'follow_up_days' => 3]);
        $this->actingAs($user)->deleteJson(route('dashboard.entries.destroy', $entry))->assertUnprocessable();
        $this->assertModelExists($entry);
        $this->deleteJson(route('dashboard.entries.destroy', $entry), ['confirmed' => true])->assertOk();
        $this->assertModelMissing($entry);
        $this->get('/dashboard?month=2026-09')->assertOk()->assertDontSee('Delete test');
    }
}
