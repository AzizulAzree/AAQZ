<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WidgetAccountWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function signedOut(): void
    {
        $this->app['session']->driver()->invalidate();
        Auth::forgetGuards();
    }

    private function token(User $user): string
    {
        $response = $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'password', 'save_account' => true]);
        $response->assertOk()->assertJsonPath('account.id', $user->id);
        $token = $response->json('token');
        $this->assertNotEmpty($token);
        $this->assertStringNotContainsString($token, json_encode(DB::table('saved_logins')->get()));

        return $token;
    }

    public function test_saved_accounts_resume_without_password_and_are_independent(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $one = $this->token($first);
        $this->signedOut();
        $two = $this->token($second);
        $this->postJson('/api/widget/logout')->assertOk();
        $this->signedOut();
        $this->postJson('/api/widget/resume', ['token' => $one])->assertOk()->assertJsonPath('account.id', $first->id);
        $this->assertAuthenticatedAs($first);
        $this->postJson('/api/widget/forget', ['token' => $two])->assertOk();
        $this->signedOut();
        $this->postJson('/api/widget/resume', ['token' => $two])->assertUnauthorized();
        $this->assertGuest();
        $this->postJson('/api/widget/resume', ['token' => $one])->assertOk();
    }

    public function test_expired_tampered_and_password_changed_tokens_fail(): void
    {
        $user = User::factory()->create();
        $token = $this->token($user);
        $this->signedOut();
        $this->postJson('/api/widget/resume', ['token' => substr($token, 0, -1).'x'])->assertUnauthorized();
        $this->travel(91)->days();
        $this->postJson('/api/widget/resume', ['token' => $token])->assertUnauthorized();
        $this->travelBack();
        $user->update(['password' => 'new-password']);
        $this->postJson('/api/widget/resume', ['token' => $token])->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_opt_out_and_bad_password_do_not_save_credentials(): void
    {
        $user = User::factory()->create();
        $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'wrong', 'save_account' => true])->assertUnprocessable();
        $this->postJson('/api/widget/login', ['email' => $user->email, 'password' => 'password'])->assertOk()->assertJsonPath('token', null);
        $this->assertDatabaseCount('saved_logins', 0);
    }

    public function test_workspace_and_note_access_is_owner_scoped_and_uncached(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $workspace = Workspace::create(['user_id' => $owner->id, 'name' => 'Owned']);
        $folder = WorkspaceNode::create(['workspace_id' => $workspace->id, 'type' => 'folder', 'name' => 'Folder']);
        $note = WorkspaceNode::create(['workspace_id' => $workspace->id, 'parent_id' => $folder->id, 'type' => 'note', 'name' => 'Note', 'content' => '<b>Private note</b>']);
        WorkspaceNode::create(['workspace_id' => $workspace->id, 'parent_id' => $folder->id, 'type' => 'shortcut', 'name' => 'Docs', 'url' => 'https://example.com']);
        Workspace::create(['user_id' => $other->id, 'name' => 'Other private workspace']);
        $owner->stickyNote()->create(['content' => 'My sticky note', 'position_x' => 0, 'position_y' => 0, 'is_collapsed' => false]);
        $this->getJson('/api/widget/workspace')->assertUnauthorized();
        $this->actingAs($owner)->getJson('/api/widget/workspace')->assertOk()->assertJsonCount(1, 'workspaces')
            ->assertJsonPath('workspaces.0.name', 'Owned')->assertJsonPath('sticky_note', 'My sticky note')
            ->assertDontSee('Private note')->assertDontSee('Other private workspace')->assertHeader('Cache-Control', 'no-store, private');
        $this->getJson('/api/widget/notes/'.$note->id)->assertOk()->assertJsonPath('content', '<b>Private note</b>');
        $this->getJson('/api/widget/notes/'.$folder->id)->assertNotFound();
        $this->actingAs($other)->getJson('/api/widget/notes/'.$note->id)->assertForbidden();
        $this->getJson('/api/widget/workspace')->assertJsonPath('sticky_note', null)->assertDontSee('Owned');
    }

    public function test_saved_account_mutations_keep_csrf_protection(): void
    {
        $this->app['env'] = 'local';
        foreach (['resume', 'forget', 'logout'] as $route) {
            $this->postJson('/api/widget/'.$route, ['token' => 'forged'])->assertStatus(419);
        }
    }
}
