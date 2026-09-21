<?php

namespace Tests\Feature\Dinas;

use App\Models\Puskesmas;
use Database\Seeders\ObservationItemSeeder;
use Database\Seeders\QuestionnaireItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([QuestionnaireItemSeeder::class, ObservationItemSeeder::class]);
    }

    public function test_summary_reflects_submission_counts(): void
    {
        $filled = Puskesmas::factory()->create();
        Puskesmas::factory()->count(2)->create();

        $this->actingAsPuskesmas($filled);
        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [['item_id' => 1, 'jawaban' => 'ya']],
        ])->assertOk();

        $this->actingAsDinas();
        $this->getJson('/api/dashboard/summary')->assertOk()->assertJson([
            'total_puskesmas' => 3,
            'kuesioner_sudah_isi' => 1,
            'kuesioner_belum_isi' => 2,
            'observasi_sudah_isi' => 0,
            'observasi_belum_isi' => 3,
        ]);
    }

    public function test_puskesmas_index_flags_correct_status(): void
    {
        $filled = Puskesmas::factory()->create(['nama' => 'UPT Puskesmas Terisi']);
        $empty = Puskesmas::factory()->create(['nama' => 'UPT Puskesmas Kosong']);

        $this->actingAsPuskesmas($filled);
        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [['item_id' => 1, 'jawaban' => 'ya']],
        ])->assertOk();

        $this->actingAsDinas();
        $response = $this->getJson('/api/dashboard/puskesmas')->assertOk();
        $data = collect($response->json('data'))->keyBy('id');

        $this->assertTrue($data[$filled->id]['kuesioner_sudah_isi']);
        $this->assertFalse($data[$empty->id]['kuesioner_sudah_isi']);
    }

    public function test_recap_counts_ya_and_tidak_correctly(): void
    {
        $p1 = Puskesmas::factory()->create();
        $p2 = Puskesmas::factory()->create();

        $this->actingAsPuskesmas($p1);
        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [['item_id' => 1, 'jawaban' => 'ya']],
        ])->assertOk();

        $this->actingAsPuskesmas($p2);
        $this->postJson('/api/questionnaire/submissions', [
            'answers' => [['item_id' => 1, 'jawaban' => 'tidak']],
        ])->assertOk();

        $this->actingAsDinas();
        $response = $this->getJson('/api/dashboard/recap')->assertOk();
        $itemA1 = collect($response->json('kuesioner'))->firstWhere('nomor', 1);

        $this->assertEquals(1, $itemA1['ya']);
        $this->assertEquals(1, $itemA1['tidak']);
    }

    public function test_puskesmas_role_cannot_access_dashboard(): void
    {
        $this->actingAsPuskesmas();

        $this->getJson('/api/dashboard/summary')->assertStatus(403);
    }
}
