<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WidgetStickyNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_widget_save_requires_login_and_validates_content(): void
    {
        $this->postJson('/api/widget/sticky-note', ['content' => 'Private'])->assertUnauthorized();
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/widget/sticky-note', [])->assertUnprocessable();
        $this->postJson('/api/widget/sticky-note', ['content' => str_repeat('x', 5001)])->assertUnprocessable();
        $this->assertDatabaseCount('sticky_notes', 0);
    }

    public function test_widget_saves_same_note_without_changing_web_layout_or_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $user->stickyNote()->create(['content' => 'Old', 'position_x' => 230, 'position_y' => 150, 'is_collapsed' => true]);
        $other->stickyNote()->create(['content' => 'Other private note']);
        $html = '<b>Bold</b><i>Italic</i><strike>Done</strike><ul><li>Bullet</li></ul><ol><li>Step</li></ol>';
        $this->actingAs($user)->postJson('/api/widget/sticky-note', ['content' => $html, 'user_id' => $other->id, 'position_x' => 0, 'is_collapsed' => false])
            ->assertOk()->assertJsonStructure(['saved_at'])->assertHeader('Cache-Control', 'no-store, private');
        $this->assertDatabaseHas('sticky_notes', ['user_id' => $user->id, 'content' => $html, 'position_x' => 230, 'position_y' => 150, 'is_collapsed' => true]);
        $this->assertDatabaseHas('sticky_notes', ['user_id' => $other->id, 'content' => 'Other private note']);
        $this->getJson('/api/widget/workspace')->assertOk()->assertJsonPath('sticky_note', $html);
        $this->postJson('/api/widget/sticky-note', ['content' => ''])->assertOk();
        $this->assertDatabaseHas('sticky_notes', ['user_id' => $user->id, 'content' => null, 'position_x' => 230]);
        $this->assertDatabaseCount('sticky_notes', 2);
    }

    public function test_first_widget_save_creates_note_with_web_defaults(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/api/widget/sticky-note', ['content' => '<p>New</p>'])->assertOk();
        $this->assertDatabaseHas('sticky_notes', ['user_id' => $user->id, 'content' => '<p>New</p>', 'position_x' => 24, 'position_y' => 96, 'is_collapsed' => false]);
    }
}
