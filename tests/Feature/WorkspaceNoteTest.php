<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceNoteTest extends TestCase
{
    use RefreshDatabase;

    private function folder(): WorkspaceNode
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Notes']);
        return WorkspaceNode::create(['workspace_id' => $workspace->id, 'type' => 'folder', 'name' => 'Planning']);
    }

    private function payload(WorkspaceNode $folder): array
    {
        return ['workspace_id' => $folder->workspace_id, 'parent_id' => $folder->id, 'type' => 'note', 'name' => 'Meeting', 'content' => "First line\n<script>alert('plain text')</script>"];
    }

    public function test_note_can_be_created_listed_and_edited_as_plain_text(): void
    {
        $folder = $this->folder();
        $payload = $this->payload($folder);
        $this->postJson(route('project.nodes.store'), $payload)->assertCreated();
        $note = WorkspaceNode::where('type', 'note')->sole();
        $this->assertSame($payload['content'], $note->content);
        $this->assertNull($note->url);
        $this->get(route('project.index'))->assertOk()->assertViewHas('workspaces', function ($workspaces) use ($note) {
            return $workspaces[0]['note_count'] === 1 && $workspaces[0]['folders'][0]['children'][0]['type'] === 'note'
                && $workspaces[0]['folders'][0]['children'][0]['content'] === $note->content;
        });
        $this->patchJson(route('project.nodes.update', $note), ['name' => 'Updated', 'content' => "Updated\ntext", 'parent_id' => null, 'type' => 'shortcut'])->assertOk();
        $this->assertSame("Updated\ntext", $note->fresh()->content);
        $this->assertSame($folder->id, $note->fresh()->parent_id);
        $this->assertSame('note', $note->fresh()->type);
        $this->get(route('project.shortcuts.open', $note))->assertNotFound();
    }

    public function test_note_requires_title_body_and_a_folder_in_the_same_workspace(): void
    {
        $folder = $this->folder();
        $payload = $this->payload($folder);
        foreach (['name', 'content', 'parent_id'] as $field) {
            $this->postJson(route('project.nodes.store'), array_replace($payload, [$field => null]))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->postJson(route('project.nodes.store'), array_replace($payload, ['content' => str_repeat('a', 100001)]))->assertUnprocessable()->assertJsonValidationErrors('content');
        $otherWorkspace = Workspace::create(['user_id' => $folder->workspace->user_id, 'name' => 'Other']);
        $this->postJson(route('project.nodes.store'), array_replace($payload, ['workspace_id' => $otherWorkspace->id]))->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $note = WorkspaceNode::create($payload);
        $this->postJson(route('project.nodes.store'), array_replace($payload, ['parent_id' => $note->id]))->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->patchJson(route('project.nodes.update', $note), ['name' => 'Title', 'content' => ''])->assertUnprocessable()->assertJsonValidationErrors('content');
    }

    public function test_other_users_cannot_read_create_edit_or_delete_notes_in_a_workspace(): void
    {
        $folder = $this->folder();
        $note = WorkspaceNode::create($this->payload($folder));
        $this->actingAs(User::factory()->create());
        $this->get(route('project.index'))->assertViewHas('workspaces', fn ($items) => $items->isEmpty());
        $this->postJson(route('project.nodes.store'), $this->payload($folder))->assertUnprocessable()->assertJsonValidationErrors('workspace_id');
        $this->patchJson(route('project.nodes.update', $note), ['name' => 'Changed', 'content' => 'Changed'])->assertForbidden();
        $this->deleteJson(route('project.nodes.destroy', $note), ['confirmed' => true])->assertForbidden();
    }

    public function test_note_deletion_needs_confirmation_and_folder_deletion_cascades(): void
    {
        $folder = $this->folder();
        $note = WorkspaceNode::create($this->payload($folder));
        $this->deleteJson(route('project.nodes.destroy', $note))->assertUnprocessable();
        $this->assertModelExists($note);
        $this->deleteJson(route('project.nodes.destroy', $note), ['confirmed' => true])->assertOk();
        $this->assertModelMissing($note);
        $second = WorkspaceNode::create($this->payload($folder));
        $this->deleteJson(route('project.nodes.destroy', $folder), ['confirmed' => true])->assertOk();
        $this->assertModelMissing($second);
    }
}
