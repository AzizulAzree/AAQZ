<?php

namespace Tests\Feature;

use App\Models\StickyNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StickyNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_save_sticky_note_state(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->putJson('/sticky-note', [
                'content' => 'Call the clinic and confirm the appointment.',
                'position_x' => 180,
                'position_y' => 132,
                'is_collapsed' => false,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['saved_at']);

        $this->assertDatabaseHas('sticky_notes', [
            'user_id' => $user->id,
            'content' => 'Call the clinic and confirm the appointment.',
            'position_x' => 180,
            'position_y' => 132,
            'is_collapsed' => false,
        ]);
    }

    public function test_sticky_note_save_updates_existing_note_for_the_same_user(): void
    {
        $user = User::factory()->create();

        StickyNote::query()->create([
            'user_id' => $user->id,
            'content' => 'Old draft',
            'position_x' => 24,
            'position_y' => 96,
            'is_collapsed' => false,
        ]);

        $this->actingAs($user)->putJson('/sticky-note', [
            'content' => 'Updated draft',
            'position_x' => 240,
            'position_y' => 160,
            'is_collapsed' => true,
        ])->assertOk();

        $this->assertDatabaseCount('sticky_notes', 1);
        $this->assertDatabaseHas('sticky_notes', [
            'user_id' => $user->id,
            'content' => 'Updated draft',
            'position_x' => 240,
            'position_y' => 160,
            'is_collapsed' => true,
        ]);
    }
}
