<?php

namespace App\Support;

use App\Models\ObservationAnswer;
use App\Models\ObservationItem;
use App\Models\ObservationSubmission;
use App\Models\Puskesmas;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSubmission;
use Illuminate\Support\Collection;

class Penilaian
{
    /**
     * Kategori penilaian berdasarkan persentase pemenuhan (Ya/Ada) terhadap
     * total item aktif. Threshold sesuai ketentuan klien.
     *
     * @return array{kategori_key: string, kategori_label: string}
     */
    public static function kategori(float $persen): array
    {
        return match (true) {
            $persen >= 90 => ['kategori_key' => 'unggul', 'kategori_label' => 'Unggul / Best Practice'],
            $persen >= 80 => ['kategori_key' => 'memenuhi_standar', 'kategori_label' => 'Memenuhi Standar'],
            $persen >= 60 => ['kategori_key' => 'memenuhi_sebagian', 'kategori_label' => 'Memenuhi Sebagian'],
            $persen >= 40 => ['kategori_key' => 'perlu_perbaikan_mayor', 'kategori_label' => 'Perlu Perbaikan Mayor'],
            default => ['kategori_key' => 'tidak_memenuhi', 'kategori_label' => 'Tidak Memenuhi'],
        };
    }

    /**
     * Jumlah item kuesioner & observasi yang "berlaku" pada periode tertentu:
     * item yang masih aktif sekarang, ATAU sudah punya jawaban di periode itu
     * (supaya periode lama tetap konsisten dengan item yang berlaku saat itu).
     *
     * @return array{kuesioner: int, observasi: int, total: int}
     */
    public static function totalItemAktif(int $bulan, int $tahun): array
    {
        $qSubmissionIds = QuestionnaireSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->pluck('id');

        $oSubmissionIds = ObservationSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->pluck('id');

        $kuesioner = QuestionnaireItem::where(function ($q) use ($qSubmissionIds) {
            $q->where('aktif', true)->orWhereHas('answers', fn ($s) => $s->whereIn('submission_id', $qSubmissionIds));
        })->count();

        $observasi = ObservationItem::where(function ($q) use ($oSubmissionIds) {
            $q->where('aktif', true)->orWhereHas('answers', fn ($s) => $s->whereIn('submission_id', $oSubmissionIds));
        })->count();

        return ['kuesioner' => $kuesioner, 'observasi' => $observasi, 'total' => $kuesioner + $observasi];
    }

    /**
     * Hitung penilaian satu puskesmas untuk satu periode.
     *
     * @param  array{kuesioner: int, observasi: int, total: int}|null  $totalItem  Supaya tidak dihitung ulang saat loop banyak puskesmas.
     */
    public static function untukPuskesmas(int $puskesmasId, int $bulan, int $tahun, ?array $totalItem = null): array
    {
        $totalItem ??= self::totalItemAktif($bulan, $tahun);

        $ya = QuestionnaireAnswer::where('jawaban', 'ya')
            ->whereHas('submission', fn ($q) => $q->where('puskesmas_id', $puskesmasId)
                ->where('periode_bulan', $bulan)->where('periode_tahun', $tahun))
            ->count();

        $ada = ObservationAnswer::where('ada', 'ada')
            ->whereHas('submission', fn ($q) => $q->where('puskesmas_id', $puskesmasId)
                ->where('periode_bulan', $bulan)->where('periode_tahun', $tahun))
            ->count();

        $terpenuhi = $ya + $ada;
        $persen = $totalItem['total'] > 0 ? round($terpenuhi / $totalItem['total'] * 100, 1) : 0.0;

        return array_merge([
            'persen' => $persen,
            'kuesioner' => ['terpenuhi' => $ya, 'total' => $totalItem['kuesioner']],
            'observasi' => ['terpenuhi' => $ada, 'total' => $totalItem['observasi']],
            'total_terpenuhi' => $terpenuhi,
            'total_item' => $totalItem['total'],
        ], self::kategori($persen));
    }

    /**
     * Hitung penilaian seluruh puskesmas untuk satu periode sekaligus.
     *
     * @return Collection<int, array>
     */
    public static function untukSemuaPuskesmas(int $bulan, int $tahun): Collection
    {
        $totalItem = self::totalItemAktif($bulan, $tahun);

        return Puskesmas::orderBy('nama')->get(['id', 'nama'])->map(
            fn (Puskesmas $p) => array_merge(
                ['puskesmas_id' => $p->id, 'nama' => $p->nama],
                self::untukPuskesmas($p->id, $bulan, $tahun, $totalItem),
            ),
        );
    }

    /**
     * Ringkasan agregat (rata-rata & distribusi kategori) untuk dashboard dinas.
     */
    public static function ringkasan(int $bulan, int $tahun): array
    {
        $semua = self::untukSemuaPuskesmas($bulan, $tahun);

        $rataRata = $semua->isNotEmpty() ? round($semua->avg('persen'), 1) : 0.0;

        $distribusi = [
            'tidak_memenuhi' => 0,
            'perlu_perbaikan_mayor' => 0,
            'memenuhi_sebagian' => 0,
            'memenuhi_standar' => 0,
            'unggul' => 0,
        ];
        foreach ($semua as $p) {
            $distribusi[$p['kategori_key']]++;
        }

        return array_merge([
            'rata_rata' => $rataRata,
            'distribusi' => $distribusi,
            'list' => $semua->values(),
        ], self::kategori($rataRata));
    }
}
