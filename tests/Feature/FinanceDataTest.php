<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_page_starts_with_empty_finance_tables(): void
    {
        $owner = User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $response = $this
            ->actingAs($owner)
            ->get('/finance');

        $response->assertOk();
        $response->assertSee('No records yet');
        $response->assertSee('Salary This Month');

        $this->assertDatabaseCount('finance_periods', 0);
        $this->assertDatabaseCount('finance_commitment_categories', 0);
        $this->assertDatabaseCount('finance_period_commitments', 0);
        $this->assertDatabaseCount('finance_records', 0);
    }

    public function test_spending_record_can_be_saved_to_finance_tables(): void
    {
        $owner = User::factory()->create([
            'name' => 'Owner',
            'email' => 'owner@example.com',
        ]);

        $response = $this
            ->actingAs($owner)
            ->postJson('/finance/records', [
                'type' => 'spending',
                'date' => '2026-06-30',
                'value' => 250,
                'category' => 'Rent',
            ]);

        $response->assertOk()->assertJson(['saved' => true]);

        $this->assertDatabaseHas('finance_periods', [
            'user_id' => $owner->id,
            'period_year' => 2026,
            'period_month' => 6,
        ]);

        $this->assertDatabaseHas('finance_commitment_categories', [
            'user_id' => $owner->id,
            'name' => 'Rent',
        ]);

        $this->assertDatabaseHas('finance_period_commitments', [
            'name_snapshot' => 'Rent',
            'amount' => 250,
            'status' => 'paid',
            'paid_on' => '2026-06-30 00:00:00',
        ]);

        $this->assertDatabaseHas('finance_records', [
            'user_id' => $owner->id,
            'record_type' => 'commitment',
            'amount' => 250,
            'title' => 'Rent commitment',
        ]);

        $this->assertDatabaseHas('finance_commitment_categories', [
            'user_id' => $owner->id,
            'name' => 'Rent',
            'default_amount' => 250,
        ]);
    }
}
