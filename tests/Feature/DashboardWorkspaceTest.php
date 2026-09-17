<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_only_lists_owned_workspace_metadata(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $owner->id, 'name' => 'Owned']);
        Workspace::create(['user_id' => $other->id, 'name' => 'Private workspace']);
        $folder = WorkspaceNode::create(['workspace_id' => $workspace->id, 'type' => 'folder', 'name' => 'Folder']);
        WorkspaceNode::create(['workspace_id' => $workspace->id, 'parent_id' => $folder->id, 'type' => 'note', 'name' => 'Note', 'content' => 'Secret body']);
        $this->actingAs($owner)->get('/dashboard')->assertOk()->assertDontSee('Secret body')->assertDontSee('Private workspace')
            ->assertViewHas('workspaceWidget', fn ($items) => $items->count() === 1 && $items[0]['nodes']->count() === 2);
    }

    public function test_note_endpoint_requires_ownership_and_a_note(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $owner->id, 'name' => 'Owned']);
        $folder = WorkspaceNode::create(['workspace_id' => $workspace->id, 'type' => 'folder', 'name' => 'Folder']);
        $note = WorkspaceNode::create(['workspace_id' => $workspace->id, 'parent_id' => $folder->id, 'type' => 'note', 'name' => 'Note', 'content' => '<b>Plain text</b>']);
        $this->getJson(route('project.notes.show', $note))->assertUnauthorized();
        $this->actingAs($owner)->getJson(route('project.notes.show', $note))->assertOk()->assertJsonPath('content', '<b>Plain text</b>');
        $this->getJson(route('project.notes.show', $folder))->assertNotFound();
        $this->actingAs(User::factory()->create())->getJson(route('project.notes.show', $note))->assertForbidden();
    }
}
