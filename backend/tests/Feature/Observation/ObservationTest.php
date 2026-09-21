<?php

namespace Tests\Feature\Observation;

use App\Models\ObservationItem;
use Database\Seeders\ObservationItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ObservationItemSeeder::class);
    }

    public function test_puskesmas_can_list_96_items(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/observation/items')->assertOk()->assertJsonCount(96);
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
}
