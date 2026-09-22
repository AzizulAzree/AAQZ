<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'database']);
    }

    private function forgetSession(): void
    {
        $this->app['session']->driver()->invalidate();
        Auth::forgetGuards();
        foreach ($this->app['cookie']->getQueuedCookies() as $cookie) {
            $this->app['cookie']->unqueue($cookie->getName(), $cookie->getPath());
        }
    }

    public function test_checked_login_issues_a_persistent_cookie_and_restores_without_session(): void
    {
        $user = User::factory()->create(['remember_token' => null]);
        $cookieName = Auth::guard()->getRecallerName();
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);
        $response->assertRedirect('/dashboard');
        $cookie = $response->getCookie($cookieName, false);
        $this->assertNotNull($cookie);
        $this->assertNotEmpty($user->fresh()->remember_token);
        $this->assertEqualsWithDelta(now()->addDays(400)->timestamp, $cookie->getExpiresTime(), 5);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->forgetSession();
        $this->withUnencryptedCookie($cookieName, $cookie->getValue())->get('/profile')->assertOk();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Auth::guard()->viaRemember());
    }

    public function test_unchecked_login_does_not_restore_after_session_loss(): void
    {
        $user = User::factory()->create(['remember_token' => null]);
        $cookieName = Auth::guard()->getRecallerName();
        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);
        $response->assertCookieMissing($cookieName);
        $this->assertNull($user->fresh()->remember_token);
        $this->forgetSession();
        $this->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_remembered_login_restores_after_seven_days_without_a_session(): void
    {
        $user = User::factory()->create();
        $cookieName = Auth::guard()->getRecallerName();
        $login = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);
        $cookie = $login->getCookie($cookieName, false);
        $this->forgetSession();
        $this->travel(7)->days();

        $this->withUnencryptedCookie($cookieName, $cookie->getValue())->get('/profile')->assertOk();
        $this->assertTrue(Auth::guard()->viaRemember());
        $this->travelBack();
    }

    public function test_logout_on_another_device_currently_invalidates_first_devices_remembered_login(): void
    {
        $user = User::factory()->create();
        $cookieName = Auth::guard()->getRecallerName();
        $firstLogin = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);
        $firstCookie = $firstLogin->getCookie($cookieName, false);
        $this->forgetSession();

        // A separate browser signs in and signs out without using the first browser's cookie.
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->post('/logout')->assertRedirect('/');
        $this->forgetSession();

        $this->withUnencryptedCookie($cookieName, $firstCookie->getValue())->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_explicit_logout_invalidates_the_old_remember_cookie(): void
    {
        $user = User::factory()->create();
        $cookieName = Auth::guard()->getRecallerName();
        $login = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);
        $cookie = $login->getCookie($cookieName, false);
        $oldToken = $user->fresh()->remember_token;
        $this->withUnencryptedCookie($cookieName, $cookie->getValue())
            ->post('/logout')->assertRedirect('/')->assertCookieExpired($cookieName);
        $this->assertNotSame($oldToken, $user->fresh()->remember_token);
        $this->forgetSession();
        $this->withUnencryptedCookie($cookieName, $cookie->getValue())->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_password_does_not_issue_a_remember_cookie(): void
    {
        $user = User::factory()->create(['remember_token' => null]);
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password', 'remember' => 'on'])
            ->assertSessionHasErrors('email')->assertCookieMissing(Auth::guard()->getRecallerName());
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_tampered_remember_cookie_cannot_restore_authentication(): void
    {
        $cookieName = Auth::guard()->getRecallerName();
        $this->withUnencryptedCookie($cookieName, 'invalid-cookie')->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_account_password_change_invalidates_old_remember_cookie(): void
    {
        $user = User::factory()->create();
        $cookieName = Auth::guard()->getRecallerName();
        $login = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => 'on']);
        $cookie = $login->getCookie($cookieName, false);
        $this->put('/password', ['current_password' => 'password', 'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password'])->assertSessionHasNoErrors();
        $this->forgetSession();
        $this->withUnencryptedCookie($cookieName, $cookie->getValue())->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }
}
