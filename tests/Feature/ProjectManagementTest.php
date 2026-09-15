<?php

namespace Tests\Feature;

use App\Models\RecentShortcut;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private function items(): array
    {
        $user = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $user->id, 'name' => 'Brand', 'sort_order' => 1]);
        $folder = WorkspaceNode::create(['workspace_id' => $workspace->id, 'name' => 'Marketing', 'type' => 'folder']);
        $nested = WorkspaceNode::create(['workspace_id' => $workspace->id, 'parent_id' => $folder->id, 'name' => 'Campaign', 'type' => 'folder']);
        $link = WorkspaceNode::create(['workspace_id' => $workspace->id, 'parent_id' => $nested->id, 'name' => 'Report', 'type' => 'shortcut', 'url' => 'https://example.com']);
        RecentShortcut::create(['user_id' => $user->id, 'workspace_node_id' => $link->id, 'opened_at' => now()]);

        return [$user, $workspace, $folder, $nested, $link];
    }

    public function test_owner_can_edit_each_item_without_changing_its_location(): void
    {
        [$user, $workspace, $folder, $nested, $link] = $this->items();
        $this->actingAs($user)->patchJson(route('project.workspaces.update', $workspace), ['name' => 'Renamed'])->assertOk();
        $this->patchJson(route('project.nodes.update', $folder), ['name' => 'Assets', 'type' => 'shortcut'])->assertOk();
        $this->patchJson(route('project.nodes.update', $link), ['name' => 'Updated', 'url' => 'https://example.com/report', 'description' => 'Monthly report', 'parent_id' => null])->assertOk();
        $this->assertSame('Renamed', $workspace->fresh()->name);
        $this->assertSame('Assets', $folder->fresh()->name);
        $this->assertSame('folder', $folder->fresh()->type);
        $this->assertSame($nested->id, $link->fresh()->parent_id);
        $this->assertSame('https://example.com/report', $link->fresh()->url);
        $this->assertSame('Monthly report', $link->fresh()->description);
    }

    public function test_edits_validate_name_and_link_url(): void
    {
        [$user, $workspace, , , $link] = $this->items();
        $this->actingAs($user)->patchJson(route('project.workspaces.update', $workspace), ['name' => ''])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->patchJson(route('project.nodes.update', $link), ['name' => 'Link', 'url' => 'javascript:alert(1)'])->assertUnprocessable()->assertJsonValidationErrors('url');
        $this->assertSame('https://example.com', $link->fresh()->url);
    }

    public function test_other_users_cannot_edit_or_delete_any_item(): void
    {
        [, $workspace, $folder, , $link] = $this->items();
        $this->actingAs(User::factory()->create());
        foreach ([route('project.workspaces.update', $workspace), route('project.nodes.update', $folder), route('project.nodes.update', $link)] as $url) {
            $this->patchJson($url, ['name' => 'Changed', 'url' => 'https://example.com'])->assertForbidden();
            $this->deleteJson($url, ['confirmed' => true])->assertForbidden();
        }
        $this->assertModelExists($workspace);
        $this->assertModelExists($folder);
        $this->assertModelExists($link);
    }

    public function test_deletion_requires_confirmation(): void
    {
        [$user, $workspace, $folder, , $link] = $this->items();
        $this->actingAs($user);
        foreach ([route('project.workspaces.destroy', $workspace), route('project.nodes.destroy', $folder), route('project.nodes.destroy', $link)] as $url) {
            $this->deleteJson($url)->assertUnprocessable()->assertJsonValidationErrors('confirmed');
        }
        $this->assertModelExists($link);
    }

    public function test_folder_deletion_removes_nested_contents_and_recent_links_only(): void
    {
        [$user, $workspace, $folder, $nested, $link] = $this->items();
        $sibling = WorkspaceNode::create(['workspace_id' => $workspace->id, 'name' => 'Keep', 'type' => 'folder']);
        $this->actingAs($user)->deleteJson(route('project.nodes.destroy', $folder), ['confirmed' => true])->assertOk()->assertJsonPath('redirect', route('project.index', ['workspace' => $workspace->id]));
        foreach ([$folder, $nested, $link] as $node) {
            $this->assertModelMissing($node);
        }
        $this->assertDatabaseCount('recent_shortcuts', 0);
        $this->assertModelExists($sibling);
        $this->assertModelExists($workspace);
    }

    public function test_workspace_deletion_removes_its_contents(): void
    {
        [$user, $workspace, $folder, $nested, $link] = $this->items();
        $this->actingAs($user)->deleteJson(route('project.workspaces.destroy', $workspace), ['confirmed' => true])->assertOk();
        foreach ([$workspace, $folder, $nested, $link] as $model) {
            $this->assertModelMissing($model);
        }
        $this->assertDatabaseCount('recent_shortcuts', 0);
    }

    public function test_link_deletion_preserves_its_parent(): void
    {
        [$user, , , $nested, $link] = $this->items();
        $this->actingAs($user)->deleteJson(route('project.nodes.destroy', $link), ['confirmed' => true])->assertOk();
        $this->assertModelMissing($link);
        $this->assertModelExists($nested);
        $this->assertDatabaseCount('recent_shortcuts', 0);
    }
}
