<?php

namespace Tests\Feature\Observation;

use App\Models\ObservationAnswerAttachment;
use App\Models\ObservationItem;
use App\Models\ObservationSubmission;
use Carbon\Carbon;
use Database\Seeders\ObservationItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ObservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ObservationItemSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_puskesmas_can_list_117_items(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/observation/items')->assertOk()->assertJsonCount(117);
    }

    public function test_kondisi_matching_item_scale_is_accepted(): void
    {
        $this->actingAsPuskesmas();

        $item = ObservationItem::where('skala_kondisi', 'baik_buruk')->firstOrFail();

        $this->postJson('/api/observation/submissions', [
            'tanggal_observasi' => '2026-09-15',
            'answers' => [
                ['item_id' => $item->id, 'ada' => 'ada', 'kondisi' => 'buruk', 'keterangan' => 'Perlu perbaikan'],
            ],
        ])->assertOk();
    }

    public function test_kondisi_not_matching_item_scale_is_rejected(): void
    {
        $this->actingAsPuskesmas();

        $item = ObservationItem::where('skala_kondisi', 'baik_buruk')->firstOrFail();

        $this->postJson('/api/observation/submissions', [
            'answers' => [
                ['item_id' => $item->id, 'ada' => 'ada', 'kondisi' => 'rusak_ringan'],
            ],
        ])->assertStatus(422);
    }

    public function test_three_level_scale_item_accepts_rusak_berat(): void
    {
        $this->actingAsPuskesmas();

        $item = ObservationItem::where('skala_kondisi', 'baik_rusak_ringan_rusak_berat')->firstOrFail();

        $this->postJson('/api/observation/submissions', [
            'answers' => [
                ['item_id' => $item->id, 'ada' => 'ada', 'kondisi' => 'rusak_berat'],
            ],
        ])->assertOk();
    }

    public function test_tidak_ada_without_kondisi_is_accepted(): void
    {
        $this->actingAsPuskesmas();

        $item = ObservationItem::first();

        $this->postJson('/api/observation/submissions', [
            'answers' => [
                ['item_id' => $item->id, 'ada' => 'tidak_ada'],
            ],
        ])->assertOk();
    }

    public function test_puskesmas_can_add_a_previous_period_observation_photo_as_a_reference(): void
    {
        Carbon::setTestNow('2026-10-26 10:00:00');
        Storage::fake('local');

        $user = $this->actingAsPuskesmas();
        $item = ObservationItem::firstOrFail();
        $submission = ObservationSubmission::create([
            'puskesmas_id' => $user->puskesmas_id,
            'user_id' => $user->id,
            'periode_bulan' => 9,
            'periode_tahun' => 2026,
        ]);
        $path = "observation-attachments/{$user->puskesmas_id}/{$submission->id}/{$item->id}/facility.jpg";
        Storage::disk('local')->put($path, 'test photo');
        $source = $submission->attachments()->create([
            'item_id' => $item->id,
            'uploaded_by' => $user->id,
            'original_name' => 'facility.jpg',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 10,
        ]);

        $this->getJson("/api/observation/items/{$item->id}/attachments/reusable")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $source->id)
            ->assertJsonPath('0.periode_bulan', 9);

        $this->postJson("/api/observation/items/{$item->id}/attachments/reuse", [
            'attachment_id' => $source->id,
            'confirmed' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('reused_from_attachment_id', $source->id)
            ->assertJsonPath('reused_from_periode_bulan', 9)
            ->assertJsonPath('reuse_confirmed_by', $user->id);

        $this->getJson("/api/observation/items/{$item->id}/attachments/reusable")
            ->assertOk()
            ->assertJsonCount(0);

        $this->actingAsDinas();
        $this->getJson("/api/dashboard/recap/observasi/{$item->id}/puskesmas?bulan=10&tahun=2026")
            ->assertOk()
            ->assertJsonPath('data.0.lampiran.0.reused_from_periode_bulan', 9)
            ->assertJsonPath('data.0.lampiran.0.reused_from_periode_tahun', 2026);

        $reference = ObservationAnswerAttachment::where('reused_from_attachment_id', $source->id)->firstOrFail();
        $this->assertSame($path, $reference->path);
        Storage::disk('local')->assertExists($path);
    }
}
