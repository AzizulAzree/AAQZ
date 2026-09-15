<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MalaysiaHolidaysTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return ['data' => [
            ['date' => '2026-09-16', 'name' => 'Hari Malaysia', 'state_codes' => array_keys(config('calendar.states')), 'is_subject_to_change' => false],
            ['date' => '2026-12-11', 'name' => 'Selangor birthday', 'state_codes' => ['SGR'], 'is_subject_to_change' => true],
        ]];
    }

    public function test_normalized_holidays_are_cached_and_observances_are_separate(): void
    {
        Http::fake(['*' => Http::response($this->payload())]);
        $this->actingAs(User::factory()->create());
        $response = $this->getJson('/dashboard/holidays?year=2026')->assertOk()->assertJsonPath('status', 'ready');
        $items = collect($response->json('holidays'));
        $this->assertTrue($items->firstWhere('name', 'Hari Malaysia')['nationwide']);
        $this->assertFalse($items->firstWhere('name', 'Selangor birthday')['nationwide']);
        $this->assertTrue($items->firstWhere('name', 'Selangor birthday')['tentative']);
        $this->assertSame('observance', $items->firstWhere('name', 'Hari Guru')['kind']);
        $this->getJson('/dashboard/holidays?year=2026')->assertOk()->assertJsonCount($items->count(), 'holidays');
        Http::assertSentCount(1);
    }

    public function test_api_failure_returns_saved_dates_without_breaking_entries(): void
    {
        Http::fake(['*' => Http::sequence()->push($this->payload())->push([], 503)]);
        $this->actingAs(User::factory()->create())->getJson('/dashboard/holidays?year=2026')->assertOk();
        Cache::forget('malaysia-holidays:v1:2026');
        $this->getJson('/dashboard/holidays?year=2026')->assertOk()->assertJsonPath('status', 'stale')->assertJsonPath('holidays.0.name', 'Hari Malaysia');
        $this->get('/dashboard')->assertOk();
    }

    public function test_unavailable_and_empty_years_are_distinct(): void
    {
        Http::fake(['*' => Http::sequence()->push([], 503)->push(['data' => []])]);
        $this->actingAs(User::factory()->create())->getJson('/dashboard/holidays?year=2026')->assertOk()->assertJsonPath('status', 'unavailable');
        $this->getJson('/dashboard/holidays?year=2027')->assertOk()->assertJsonPath('status', 'empty');
    }

    public function test_malformed_dates_are_not_shown_as_holidays(): void
    {
        Http::fake(['*' => Http::response(['data' => [['date' => '2026-02-31', 'name' => 'Invalid', 'state_codes' => ['SGR']]]])]);
        $this->actingAs(User::factory()->create())->getJson('/dashboard/holidays?year=2026')->assertOk()->assertJsonPath('status', 'unavailable')->assertJsonMissing(['name' => 'Invalid']);
    }

    public function test_invalid_year_is_rejected_without_upstream_request(): void
    {
        Http::fake();
        $this->actingAs(User::factory()->create())->getJson('/dashboard/holidays?year=9999')->assertUnprocessable();
        Http::assertNothingSent();
    }
}
