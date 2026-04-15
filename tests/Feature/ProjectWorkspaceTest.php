<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_page_lists_user_workspaces(): void
    {
        $user = User::factory()->create();

        Workspace::query()->create([
            'user_id' => $user->id,
            'name' => 'Workspace Alpha',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/project');

        $response->assertOk();
        $response->assertSee('Workspace Alpha');
        $response->assertSee('Recently opened');
    }

    public function test_authenticated_user_can_create_workspace(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/project/workspaces', [
                'name' => 'Workspace One',
            ]);

        $response->assertRedirect('/project');

        $this->assertDatabaseHas('workspaces', [
            'user_id' => $user->id,
            'name' => 'Workspace One',
        ]);
    }

    public function test_authenticated_user_can_create_root_folder_in_workspace(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::query()->create([
            'user_id' => $user->id,
            'name' => 'Workspace One',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/project/nodes', [
                'workspace_id' => $workspace->id,
                'type' => 'folder',
                'name' => 'Planning',
            ]);

        $response->assertRedirect('/project');

        $this->assertDatabaseHas('workspace_nodes', [
            'workspace_id' => $workspace->id,
            'parent_id' => null,
            'type' => 'folder',
            'name' => 'Planning',
        ]);
    }

    public function test_shortcuts_must_be_created_inside_folders(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::query()->create([
            'user_id' => $user->id,
            'name' => 'Workspace One',
            'sort_order' => 1,
        ]);

        $response = $this
            ->from('/project')
            ->actingAs($user)
            ->post('/project/nodes', [
                'workspace_id' => $workspace->id,
                'type' => 'shortcut',
                'name' => 'Google',
                'url' => 'https://google.com',
            ]);

        $response->assertRedirect('/project');
        $response->assertSessionHasErrors(['parent_id']);
    }

    public function test_authenticated_user_can_create_shortcut_inside_folder(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::query()->create([
            'user_id' => $user->id,
            'name' => 'Workspace One',
            'sort_order' => 1,
        ]);

        $folder = WorkspaceNode::query()->create([
            'workspace_id' => $workspace->id,
            'parent_id' => null,
            'type' => 'folder',
            'name' => 'Planning',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/project/nodes', [
                'workspace_id' => $workspace->id,
                'parent_id' => $folder->id,
                'type' => 'shortcut',
                'name' => 'Timeline board',
                'url' => 'https://example.com/timeline',
                'description' => 'Quarterly rollout checkpoints',
            ]);

        $response->assertRedirect('/project');

        $this->assertDatabaseHas('workspace_nodes', [
            'workspace_id' => $workspace->id,
            'parent_id' => $folder->id,
            'type' => 'shortcut',
            'name' => 'Timeline board',
            'url' => 'https://example.com/timeline',
        ]);
    }

    public function test_opening_shortcut_logs_recently_opened_item(): void
    {
        $user = User::factory()->create();

        $workspace = Workspace::query()->create([
            'user_id' => $user->id,
            'name' => 'Workspace One',
            'sort_order' => 1,
        ]);

        $folder = WorkspaceNode::query()->create([
            'workspace_id' => $workspace->id,
            'parent_id' => null,
            'type' => 'folder',
            'name' => 'Planning',
            'sort_order' => 1,
        ]);

        $shortcut = WorkspaceNode::query()->create([
            'workspace_id' => $workspace->id,
            'parent_id' => $folder->id,
            'type' => 'shortcut',
            'name' => 'Timeline board',
            'url' => 'https://example.com/timeline',
            'sort_order' => 1,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('project.shortcuts.open', $shortcut));

        $response->assertRedirect('https://example.com/timeline');

        $this->assertDatabaseHas('recent_shortcuts', [
            'user_id' => $user->id,
            'workspace_node_id' => $shortcut->id,
        ]);
    }
}
