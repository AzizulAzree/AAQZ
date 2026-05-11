<?php

namespace Tests\Feature;

use Tests\TestCase;

class PortfolioTest extends TestCase
{
    public function test_portfolio_shortcut_redirects_to_the_public_slug(): void
    {
        $response = $this->get('/portfolio');

        $response->assertRedirect('/azizulazree');
    }

    public function test_public_portfolio_page_is_accessible_without_authentication(): void
    {
        $response = $this->get('/azizulazree');

        $response->assertOk();
        $response->assertSee('Portfolio scroll template');
        $response->assertSee('portfolio-stage');
        $response->assertSee('IntersectionObserver');
    }

    public function test_legacy_portfolio_slug_redirects_to_new_public_path(): void
    {
        $response = $this->get('/portfolio/azizul-azree');

        $response->assertRedirect('/azizulazree');
    }
}
