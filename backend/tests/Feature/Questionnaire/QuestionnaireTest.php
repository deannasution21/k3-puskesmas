<?php

namespace Tests\Feature\Questionnaire;

use App\Models\Puskesmas;
use App\Models\QuestionnaireAnswerAttachment;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSubmission;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\QuestionnaireItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(QuestionnaireItemSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_puskesmas_can_list_56_active_items(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/questionnaire/items')->assertOk()->assertJsonCount(56);
    }

    public function test_nonaktif_item_is_excluded_from_active_list(): void
    {
        $this->actingAsPuskesmas();

        $nonaktif = QuestionnaireItem::where('aktif', false)->firstOrFail();

        $response = $this->getJson('/api/questionnaire/items')->assertOk();
        $ids = collect($response->json())->pluck('id');

        $this->assertFalse($ids->contains($nonaktif->id));
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

    public function test_puskesmas_can_reuse_a_same_item_attachment_from_a_previous_period(): void
    {
        Carbon::setTestNow('2026-10-26 10:00:00');
        Storage::fake('local');

        $user = $this->actingAsPuskesmas();
        $item = QuestionnaireItem::firstOrFail();
        $submission = QuestionnaireSubmission::create([
            'puskesmas_id' => $user->puskesmas_id,
            'user_id' => $user->id,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
        ]);
        $path = "questionnaire-attachments/{$user->puskesmas_id}/{$submission->id}/{$item->id}/sop.pdf";
        Storage::disk('local')->put($path, 'test evidence');
        $source = $submission->attachments()->create([
            'item_id' => $item->id,
            'uploaded_by' => $user->id,
            'original_name' => 'sop.pdf',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 13,
        ]);

        $this->getJson("/api/questionnaire/items/{$item->id}/attachments/reusable")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $source->id)
            ->assertJsonPath('0.periode_bulan', 9)
            ->assertJsonPath('0.periode_tahun', 2026);

        $this->postJson("/api/questionnaire/items/{$item->id}/attachments/reuse", [
            'attachment_id' => $source->id,
            'confirmed' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('reused_from_attachment_id', $source->id)
            ->assertJsonPath('reused_from_periode_bulan', 9)
            ->assertJsonPath('reused_from_periode_tahun', 2026)
            ->assertJsonPath('reuse_confirmed_by', $user->id);

        $this->getJson("/api/questionnaire/items/{$item->id}/attachments/reusable")
            ->assertOk()
            ->assertJsonCount(0);
        $this->postJson("/api/questionnaire/items/{$item->id}/attachments/reuse", [
            'attachment_id' => $source->id,
            'confirmed' => true,
        ])->assertStatus(409);

        $this->getJson('/api/questionnaire/submissions/current')
            ->assertOk()
            ->assertJsonPath('submission.attachments.0.reused_from_periode_bulan', 9);

        $reused = QuestionnaireAnswerAttachment::where('reused_from_attachment_id', $source->id)->firstOrFail();
        $this->assertSame($path, $reused->path);
        Storage::disk('local')->assertExists($path);
    }

    public function test_puskesmas_cannot_reuse_an_attachment_without_confirmation_or_from_another_puskesmas(): void
    {
        Carbon::setTestNow('2026-10-26 10:00:00');
        Storage::fake('local');

        $this->actingAsPuskesmas();
        $otherPuskesmas = Puskesmas::factory()->create();
        $otherUser = User::factory()->create([
            'role' => 'puskesmas',
            'puskesmas_id' => $otherPuskesmas->id,
        ]);
        $item = QuestionnaireItem::firstOrFail();
        $submission = QuestionnaireSubmission::create([
            'puskesmas_id' => $otherPuskesmas->id,
            'user_id' => $otherUser->id,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
        ]);
        $path = "questionnaire-attachments/{$otherPuskesmas->id}/{$submission->id}/{$item->id}/sop.pdf";
        Storage::disk('local')->put($path, 'test evidence');
        $source = $submission->attachments()->create([
            'item_id' => $item->id,
            'uploaded_by' => $otherUser->id,
            'original_name' => 'sop.pdf',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 13,
        ]);

        $this->postJson("/api/questionnaire/items/{$item->id}/attachments/reuse", [
            'attachment_id' => $source->id,
        ])->assertUnprocessable();

        $this->postJson("/api/questionnaire/items/{$item->id}/attachments/reuse", [
            'attachment_id' => $source->id,
            'confirmed' => true,
        ])->assertNotFound();
    }

    public function test_deleting_the_original_submission_keeps_a_reused_file_available(): void
    {
        Carbon::setTestNow('2026-10-26 10:00:00');
        Storage::fake('local');

        $user = $this->actingAsPuskesmas();
        $item = QuestionnaireItem::firstOrFail();
        $submission = QuestionnaireSubmission::create([
            'puskesmas_id' => $user->puskesmas_id,
            'user_id' => $user->id,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
        ]);
        $path = "questionnaire-attachments/{$user->puskesmas_id}/{$submission->id}/{$item->id}/sop.pdf";
        Storage::disk('local')->put($path, 'test evidence');
        $source = $submission->attachments()->create([
            'item_id' => $item->id,
            'uploaded_by' => $user->id,
            'original_name' => 'sop.pdf',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 13,
        ]);

        $this->postJson("/api/questionnaire/items/{$item->id}/attachments/reuse", [
            'attachment_id' => $source->id,
            'confirmed' => true,
        ])->assertCreated();

        $this->actingAsDinas();
        $this->deleteJson("/api/dashboard/puskesmas/{$user->puskesmas_id}/questionnaire/2026-09")
            ->assertOk();

        $reused = QuestionnaireAnswerAttachment::where('reused_from_periode_tahun', 2026)->firstOrFail();
        $this->assertSame($source->id, $reused->reused_from_attachment_id);
        $this->assertSame(9, $reused->reused_from_periode_bulan);
        Storage::disk('local')->assertExists($path);
    }
}
