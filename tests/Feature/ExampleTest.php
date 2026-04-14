<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guests_are_redirected_to_the_login_screen_from_home(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login', absolute: false));
    }
}
