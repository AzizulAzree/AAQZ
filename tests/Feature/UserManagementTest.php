<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_page_requires_authentication(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_the_user_management_page(): void
    {
        $adminUser = User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $managedUser = User::factory()->create([
            'name' => 'Jane Example',
            'email' => 'jane@example.com',
        ]);

        $response = $this
            ->actingAs($adminUser)
            ->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Settings');
        $response->assertSee($managedUser->email);
    }

    public function test_authenticated_users_can_add_a_new_user(): void
    {
        $adminUser = User::factory()->create(['color' => '#3B82F6']);

        $response = $this
            ->actingAs($adminUser)
            ->post('/admin/users', [
                'name' => 'New User',
                'email' => 'new-user@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('status', 'user-created');

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'new-user@example.com',
        ]);

        $createdUser = User::query()->where('email', 'new-user@example.com')->first();

        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->color);
        $this->assertNotSame($adminUser->color, $createdUser->color);
        $this->assertSame('#3B82F6', $adminUser->fresh()->color);
    }

    public function test_user_creation_requires_a_unique_email(): void
    {
        $adminUser = User::factory()->create();
        User::factory()->create([
            'email' => 'taken@example.com',
        ]);

        $response = $this
            ->actingAs($adminUser)
            ->from(route('users.index'))
            ->post('/admin/users', [
                'name' => 'Duplicate User',
                'email' => 'taken@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasErrors('email');
    }

    public function test_only_the_first_user_can_view_the_user_management_page(): void
    {
        User::factory()->create([
            'name' => 'First User',
            'email' => 'first@example.com',
        ]);

        $nonAdminUser = User::factory()->create([
            'name' => 'Second User',
            'email' => 'second@example.com',
        ]);

        $response = $this
            ->actingAs($nonAdminUser)
            ->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_only_the_first_user_can_create_users(): void
    {
        User::factory()->create([
            'name' => 'First User',
            'email' => 'first@example.com',
        ]);

        $nonAdminUser = User::factory()->create([
            'name' => 'Second User',
            'email' => 'second@example.com',
        ]);

        $response = $this
            ->actingAs($nonAdminUser)
            ->post('/admin/users', [
                'name' => 'Blocked User',
                'email' => 'blocked@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'blocked@example.com',
        ]);
    }
}
