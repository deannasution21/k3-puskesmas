<?php

namespace Database\Seeders;

use App\Models\ObservationItem;
use App\Models\Puskesmas;
use App\Models\QuestionnaireItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyHistorySeeder extends Seeder
{
    /**
     * Data dummy pengisian kuesioner & observasi untuk 3 bulan ke belakang
     * (tidak menyentuh bulan berjalan), supaya riwayat & rekap punya isi untuk dilihat.
     * Jalankan manual: php artisan db:seed --class=DummyHistorySeeder
     */
    public function run(): void
    {
        $puskesmasList = Puskesmas::with('user')->get();
        $questionnaireItems = QuestionnaireItem::pluck('id');
        $observationItems = ObservationItem::all(['id', 'skala_kondisi']);

        $kondisiOptions = [
            'baik_buruk' => ['baik', 'baik', 'baik', 'buruk'],
            'baik_rusak_ringan_rusak_berat' => ['baik', 'baik', 'baik', 'rusak_ringan', 'rusak_berat'],
        ];

        foreach ([1, 2, 3] as $monthsAgo) {
            $date = Carbon::now()->subMonths($monthsAgo);
            $bulan = $date->month;
            $tahun = $date->year;
            $tanggalObservasi = $date->copy()->startOfMonth()->addDays(random_int(4, 24))->toDateString();

            foreach ($puskesmasList as $puskesmas) {
                if (! $puskesmas->user) {
                    continue;
                }

                if (random_int(1, 100) <= 85) {
                    $this->seedQuestionnaire($puskesmas, $bulan, $tahun, $questionnaireItems);
                }

                if (random_int(1, 100) <= 80) {
                    $this->seedObservation($puskesmas, $bulan, $tahun, $tanggalObservasi, $observationItems, $kondisiOptions);
                }
            }
        }
    }

    private function seedQuestionnaire(Puskesmas $puskesmas, int $bulan, int $tahun, $items): void
    {
        $submissionId = DB::table('questionnaire_submissions')->insertGetId([
            'puskesmas_id' => $puskesmas->id,
            'user_id' => $puskesmas->user->id,
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rows = [];
        foreach ($items as $itemId) {
            $rows[] = [
                'submission_id' => $submissionId,
                'item_id' => $itemId,
                'jawaban' => random_int(1, 100) <= 70 ? 'ya' : 'tidak',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('questionnaire_answers')->insert($rows);
    }

    private function seedObservation(Puskesmas $puskesmas, int $bulan, int $tahun, string $tanggalObservasi, $items, array $kondisiOptions): void
    {
        $submissionId = DB::table('observation_submissions')->insertGetId([
            'puskesmas_id' => $puskesmas->id,
            'user_id' => $puskesmas->user->id,
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'tanggal_observasi' => $tanggalObservasi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rows = [];
        foreach ($items as $item) {
            $ada = random_int(1, 100) <= 80;
            $kondisi = $ada ? $kondisiOptions[$item->skala_kondisi][array_rand($kondisiOptions[$item->skala_kondisi])] : null;

            $rows[] = [
                'submission_id' => $submissionId,
                'item_id' => $item->id,
                'ada' => $ada ? 'ada' : 'tidak_ada',
                'kondisi' => $kondisi,
                'keterangan' => $kondisi === 'buruk' || $kondisi === 'rusak_berat' ? 'Perlu tindak lanjut segera.' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('observation_answers')->insert($rows);
    }
}
