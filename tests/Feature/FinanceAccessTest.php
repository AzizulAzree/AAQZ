<?php

namespace Tests\Feature;

use App\Models\FinancePageAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_page_requires_authentication(): void
    {
        $response = $this->get('/finance');

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_open_finance_page_and_manage_access(): void
    {
        $adminUser = User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $allowedUser = User::factory()->create([
            'name' => 'Jane Example',
            'email' => 'jane@example.com',
        ]);

        $response = $this
            ->actingAs($adminUser)
            ->get('/finance');

        $response->assertOk();
        $response->assertSee('Access Settings');
        $response->assertSee($allowedUser->email);
    }

    public function test_non_selected_user_cannot_open_finance_page(): void
    {
        User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $nonAllowedUser = User::factory()->create([
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
        ]);

        $response = $this
            ->actingAs($nonAllowedUser)
            ->get('/finance');

        $response->assertForbidden();
    }

    public function test_selected_user_can_open_finance_page(): void
    {
        User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $allowedUser = User::factory()->create([
            'name' => 'Allowed User',
            'email' => 'allowed@example.com',
        ]);

        FinancePageAccess::query()->create([
            'user_id' => $allowedUser->id,
        ]);

        $response = $this
            ->actingAs($allowedUser)
            ->get('/finance');

        $response->assertOk();
        $response->assertDontSee('Access Settings');
    }

    public function test_earliest_remaining_user_always_keeps_finance_access(): void
    {
        $deletedOwner = User::factory()->create([
            'name' => 'Deleted Owner',
            'email' => 'deleted-owner@example.com',
        ]);

        $newEarliestUser = User::factory()->create([
            'name' => 'New Earliest',
            'email' => 'new-earliest@example.com',
        ]);

        $deletedOwner->delete();

        $response = $this
            ->actingAs($newEarliestUser->fresh())
            ->get('/finance');

        $response->assertOk();
        $response->assertSee('Access Settings');
    }

    public function test_admin_can_update_finance_access(): void
    {
        $adminUser = User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $allowedUser = User::factory()->create([
            'name' => 'Allowed User',
            'email' => 'allowed@example.com',
        ]);

        $blockedUser = User::factory()->create([
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
        ]);

        $response = $this
            ->actingAs($adminUser)
            ->put('/finance/access', [
                'user_ids' => [$allowedUser->id],
            ]);

        $response->assertRedirect(route('finance.index'));
        $response->assertSessionHas('status', 'finance-access-updated');

        $this->assertDatabaseHas('finance_page_accesses', [
            'user_id' => $allowedUser->id,
        ]);

        $this->assertDatabaseMissing('finance_page_accesses', [
            'user_id' => $blockedUser->id,
        ]);
    }

    public function test_non_admin_cannot_update_finance_access(): void
    {
        User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $nonAdminUser = User::factory()->create([
            'name' => 'Second User',
            'email' => 'second@example.com',
        ]);

        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@example.com',
        ]);

        $response = $this
            ->actingAs($nonAdminUser)
            ->put('/finance/access', [
                'user_ids' => [$targetUser->id],
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('finance_page_accesses', [
            'user_id' => $targetUser->id,
        ]);
    }

    public function test_finance_tab_is_hidden_for_user_without_access(): void
    {
        User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $nonAllowedUser = User::factory()->create([
            'name' => 'Blocked User',
            'email' => 'blocked@example.com',
        ]);

        $response = $this
            ->actingAs($nonAllowedUser)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Finance');
    }

    public function test_finance_tab_is_visible_for_selected_user(): void
    {
        User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $allowedUser = User::factory()->create([
            'name' => 'Allowed User',
            'email' => 'allowed@example.com',
        ]);

        FinancePageAccess::query()->create([
            'user_id' => $allowedUser->id,
        ]);

        $response = $this
            ->actingAs($allowedUser)
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Finance');
    }
}
