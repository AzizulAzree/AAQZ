<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\SavedLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SavedLoginTest extends TestCase
{
    use RefreshDatabase;

    private function newBrowserRequest(): void
    {
        $this->app['session']->driver()->invalidate();
        Auth::forgetGuards();
        foreach ($this->app['cookie']->getQueuedCookies() as $cookie) {
            $this->app['cookie']->unqueue($cookie->getName(), $cookie->getPath());
        }
    }

    private function saveAccount(User $user): string
    {
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => '1']);
        $response->assertRedirect('/dashboard');
        $cookie = $response->getCookie(SavedLogin::COOKIE, false);
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $cookie->getExpiresTime(), 5);

        return $cookie->getValue();
    }

    public function test_card_survives_logout_and_signs_in_without_credentials(): void
    {
        $user = User::factory()->create();
        $cookie = $this->saveAccount($user);
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->post('/logout')->assertRedirect('/');
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->get('/login')
            ->assertOk()->assertSee('Continue as '.$user->name)->assertHeader('Cache-Control', 'no-store, private');
        $this->post('/login/saved')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('saved_logins', 1);

        // A successful continuation replaces the device credential; the previous one no longer works.
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->post('/login/saved')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_forgotten_card_cannot_be_replayed(): void
    {
        $cookie = $this->saveAccount(User::factory()->create());
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->delete('/login/saved')
            ->assertRedirect('/login')->assertCookieExpired(SavedLogin::COOKIE);
        $this->assertDatabaseCount('saved_logins', 0);
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->post('/login/saved')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_expired_or_changed_password_cards_require_password_login(): void
    {
        $user = User::factory()->create();
        $cookie = $this->saveAccount($user);
        $this->newBrowserRequest();
        $this->travel(91)->days();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->post('/login/saved')->assertRedirect('/login');
        $this->assertGuest();
        $this->travelBack();

        $cookie = $this->saveAccount($user);
        $user->update(['password' => Hash::make('changed-password')]);
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->get('/login')->assertDontSee('Continue as');
        $this->post('/login/saved')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_another_devices_logout_does_not_revoke_the_card(): void
    {
        $user = User::factory()->create();
        $cookie = $this->saveAccount($user);
        $this->newBrowserRequest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->post('/logout');
        $this->newBrowserRequest();
        $this->travel(7)->days();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->post('/login/saved')->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->travelBack();
    }

    public function test_missing_forged_or_deleted_user_cannot_sign_in(): void
    {
        $this->post('/login/saved', ['user_id' => 1])->assertRedirect('/login');
        $this->assertGuest();
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, 'forged')->post('/login/saved')->assertRedirect('/login');
        $this->assertGuest();

        $user = User::factory()->create();
        $cookie = $this->saveAccount($user);
        $user->delete();
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie)->post('/login/saved')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseCount('saved_logins', 0);
    }

    public function test_unchecked_or_failed_login_does_not_save_an_account(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong', 'remember' => '1'])->assertSessionHasErrors();
        $this->assertDatabaseCount('saved_logins', 0);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertDatabaseCount('saved_logins', 0);
    }

    public function test_saving_another_account_replaces_the_previous_card(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();
        $cookie = $this->saveAccount($first);
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $cookie);
        $newCookie = $this->saveAccount($second);
        $this->assertDatabaseCount('saved_logins', 1);
        $this->assertSame($second->id, DB::table('saved_logins')->value('user_id'));
        $this->newBrowserRequest();
        $this->withUnencryptedCookie(SavedLogin::COOKIE, $newCookie)->get('/login')
            ->assertSee('Continue as '.$second->name)->assertDontSee('Continue as '.$first->name);
    }
}
