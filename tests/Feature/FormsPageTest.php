<?php

namespace Tests\Feature;

use App\Models\OrderingSmartForm;
use App\Models\OrderingSmartFormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_responses_are_owned_paginated_and_have_readable_dates(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        for ($i = 0; $i < 21; $i++) {
            OrderingSmartFormSubmission::create(['user_id' => $user->id, 'status' => 'submitted', 'submitted_at' => now()->subDays(3), 'payload' => [
                ['id' => 1, 'title' => 'Nama', 'type' => 'text', 'value' => 'Customer'],
                ['id' => 2, 'title' => 'Optional', 'type' => 'text', 'value' => null],
            ]]);
        }
        OrderingSmartFormSubmission::create(['user_id' => $other->id, 'status' => 'submitted', 'submitted_at' => now(), 'payload' => [['id' => 1, 'title' => 'Name', 'value' => 'Private other response']]]);
        $response = $this->actingAs($user)->get('/form')->assertOk()->assertSee('Edit form')->assertDontSee('Private other response');
        $response->assertViewHas('submissionPages', fn ($pages) => $pages->total() === 21);
        $response->assertViewHas('submissions', fn ($rows) => count($rows) === 20 && $rows[0]['day_submitted'] === '3 days ago' && $rows[0]['answer_count'] === 1);
        $this->get('/form?page=2')->assertOk()->assertViewHas('submissions', fn ($rows) => count($rows) === 1);
    }

    public function test_editor_saves_order_and_existing_response_snapshots_remain_intact(): void
    {
        $user = User::factory()->create();
        $submission = OrderingSmartFormSubmission::create(['user_id' => $user->id, 'status' => 'submitted', 'submitted_at' => now(), 'payload' => [['id' => 1, 'title' => 'Original', 'value' => 'Answer']]]);
        $rows = [['id' => 2, 'title' => 'Choices', 'type' => 'dropdown', 'options_text' => "Red, large\nBlue"], ['id' => 1, 'title' => 'Renamed', 'type' => 'text', 'options_text' => '']];
        $this->actingAs($user)->postJson('/form', ['rows' => json_encode($rows)])->assertOk()->assertJsonPath('rows.0.id', 2);
        $this->assertSame([2, 1], array_column($user->orderingSmartForm->fields, 'id'));
        $this->assertSame('Original', $submission->fresh()->payload[0]['title']);
        $this->postJson('/form', ['rows' => json_encode([['id' => 1, 'title' => '', 'type' => 'text']])])->assertUnprocessable();
        $this->postJson('/form', ['rows' => 'invalid'])->assertUnprocessable();
        $this->assertSame([2, 1], array_column($user->orderingSmartForm->fresh()->fields, 'id'));
    }

    public function test_public_form_accepts_existing_field_types_and_comma_options(): void
    {
        $user = User::factory()->create();
        OrderingSmartForm::create(['user_id' => $user->id, 'fields' => [
            ['id' => 1, 'title' => 'Choice', 'type' => 'dropdown', 'options_text' => "Red, large\nBlue"],
            ['id' => 2, 'title' => 'Quantity', 'type' => 'number', 'options_text' => ''],
            ['id' => 3, 'title' => 'Extras', 'type' => 'checkbox', 'options_text' => "A\nB"],
        ]]);
        $token = substr(hash_hmac('sha256', 'ordering-smart-form|'.$user->id.'|'.$user->email, config('app.key')), 0, 24);
        $url = '/ordering/'.$user->id.'/'.$token;
        $this->get($url)->assertOk()->assertSee('Send response')->assertSee('Red, large');
        $this->post($url, ['answers' => [1 => 'Red, large', 2 => 2, 3 => ['A', 'B']]])->assertRedirect($url)->assertSessionHas('status', 'submitted');
        $this->assertDatabaseCount('ordering_smart_form_submissions', 1);
        $this->post($url, ['answers' => [1 => 'Not allowed']])->assertSessionHasErrors('answers.1');
        $this->get('/ordering/'.$user->id.'/wrong-token')->assertNotFound();
    }

    public function test_required_questions_are_saved_and_enforced_on_public_submissions(): void
    {
        $user = User::factory()->create();
        $rows = [
            ['id' => 1, 'title' => 'Name', 'type' => 'text', 'required' => true, 'options_text' => ''],
            ['id' => 2, 'title' => 'Select extras', 'type' => 'checkbox', 'required' => true, 'options_text' => "A\nB"],
            ['id' => 3, 'title' => 'Optional', 'type' => 'number', 'options_text' => ''],
        ];
        $this->actingAs($user)->postJson('/form', ['rows' => json_encode($rows)])
            ->assertOk()->assertJsonPath('rows.0.required', true)->assertJsonPath('rows.2.required', false);
        $token = substr(hash_hmac('sha256', 'ordering-smart-form|'.$user->id.'|'.$user->email, config('app.key')), 0, 24);
        $url = '/ordering/'.$user->id.'/'.$token;
        $this->get($url)->assertOk()->assertSee('aria-label="Required"', false);
        $this->post($url, ['answers' => []])->assertSessionHasErrors(['answers.1', 'answers.2']);
        $this->assertDatabaseCount('ordering_smart_form_submissions', 0);
        $this->post($url, ['answers' => [1 => 'Alex', 2 => ['A']]])->assertSessionHasNoErrors()->assertRedirect($url);
        $this->assertDatabaseCount('ordering_smart_form_submissions', 1);
    }
}
