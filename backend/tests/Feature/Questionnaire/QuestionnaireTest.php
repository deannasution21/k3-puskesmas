<?php

namespace Tests\Feature\Questionnaire;

use App\Models\Puskesmas;
use Database\Seeders\QuestionnaireItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(QuestionnaireItemSeeder::class);
    }

    public function test_puskesmas_can_list_21_items(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/questionnaire/items')->assertOk()->assertJsonCount(21);
    }

    public function test_current_submission_is_null_when_not_yet_filled(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/questionnaire/submissions/current')
            ->assertOk()
            ->assertJsonPath('submission', null);
    }

    public function test_puskesmas_can_submit_and_read_back_answers(): void
    {
        $this->actingAsPuskesmas();

        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [
                ['item_id' => 1, 'jawaban' => 'ya'],
                ['item_id' => 2, 'jawaban' => 'tidak'],
            ],
        ])->assertOk();

        $response = $this->getJson('/api/questionnaire/submissions/current')->assertOk();
        $answers = $response->json('submission.answers');

        $this->assertCount(2, $answers);
    }

    public function test_submission_is_scoped_to_own_puskesmas(): void
    {
        $puskesmasA = Puskesmas::factory()->create();
        $puskesmasB = Puskesmas::factory()->create();

        $this->actingAsPuskesmas($puskesmasA);
        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [['item_id' => 1, 'jawaban' => 'ya']],
        ])->assertOk();

        $this->actingAsPuskesmas($puskesmasB);
        $this->getJson('/api/questionnaire/submissions/current')
            ->assertOk()
            ->assertJsonPath('submission', null);
    }

    public function test_invalid_jawaban_value_is_rejected(): void
    {
        $this->actingAsPuskesmas();

        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [['item_id' => 1, 'jawaban' => 'mungkin']],
        ])->assertStatus(422);
    }

    public function test_dinas_cannot_access_puskesmas_questionnaire_endpoints(): void
    {
        $this->actingAsDinas();

        $this->getJson('/api/questionnaire/items')->assertStatus(403);
    }

    public function test_guest_cannot_access_questionnaire_endpoints(): void
    {
        $this->getJson('/api/questionnaire/items')->assertStatus(401);
    }
}
