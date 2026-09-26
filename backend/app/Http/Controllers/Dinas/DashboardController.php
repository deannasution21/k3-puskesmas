<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\ObservationItem;
use App\Models\ObservationSubmission;
use App\Models\Puskesmas;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSubmission;
use App\Support\Periode;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request)
    {
        $bulan = (int) $request->query('bulan', Periode::bulanSekarang());
        $tahun = (int) $request->query('tahun', Periode::tahunSekarang());

        $totalPuskesmas = Puskesmas::count();

        $sudahKuesioner = QuestionnaireSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->distinct('puskesmas_id')->count('puskesmas_id');

        $sudahObservasi = ObservationSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->distinct('puskesmas_id')->count('puskesmas_id');

        return response()->json([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'total_puskesmas' => $totalPuskesmas,
            'kuesioner_sudah_isi' => $sudahKuesioner,
            'kuesioner_belum_isi' => $totalPuskesmas - $sudahKuesioner,
            'observasi_sudah_isi' => $sudahObservasi,
            'observasi_belum_isi' => $totalPuskesmas - $sudahObservasi,
        ]);
    }

    public function puskesmasIndex(Request $request)
    {
        $bulan = (int) $request->query('bulan', Periode::bulanSekarang());
        $tahun = (int) $request->query('tahun', Periode::tahunSekarang());

        $questionnaireDone = QuestionnaireSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->pluck('puskesmas_id')->flip();

        $observationDone = ObservationSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->pluck('puskesmas_id')->flip();

        $puskesmas = Puskesmas::orderBy('nama')->get()->map(fn (Puskesmas $p) => [
            'id' => $p->id,
            'nama' => $p->nama,
            'alamat' => $p->alamat,
            'kepala_puskesmas' => $p->kepala_puskesmas,
            'no_hp' => $p->no_hp,
            'email' => $p->email,
            'kode_puskesmas' => $p->kode_puskesmas,
            'kuesioner_sudah_isi' => $questionnaireDone->has($p->id),
            'observasi_sudah_isi' => $observationDone->has($p->id),
        ]);

        return response()->json([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'data' => $puskesmas,
        ]);
    }

    public function puskesmasShow(Puskesmas $puskesmas)
    {
        return response()->json([
            'puskesmas' => $puskesmas->load('user:id,username,puskesmas_id'),
            'kuesioner_history' => $puskesmas->questionnaireSubmissions()
                ->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')
                ->withCount('answers')->get(),
            'observasi_history' => $puskesmas->observationSubmissions()
                ->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')
                ->withCount('answers')->get(),
        ]);
    }

    public function questionnaireDetail(Puskesmas $puskesmas, string $periode)
    {
        [$bulan, $tahun] = Periode::parse($periode);

        $submission = $puskesmas->questionnaireSubmissions()
            ->with('answers.item')
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->firstOrFail();

        return response()->json($submission);
    }

    public function observationDetail(Puskesmas $puskesmas, string $periode)
    {
        [$bulan, $tahun] = Periode::parse($periode);

        $submission = $puskesmas->observationSubmissions()
            ->with('answers.item')
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->firstOrFail();

        return response()->json($submission);
    }

    public function recap(Request $request)
    {
        $bulan = (int) $request->query('bulan', Periode::bulanSekarang());
        $tahun = (int) $request->query('tahun', Periode::tahunSekarang());

        $submissionIds = QuestionnaireSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->pluck('id');

        $questionnaireRecap = QuestionnaireItem::orderBy('kategori_kode')->orderBy('nomor')->get()
            ->map(function (QuestionnaireItem $item) use ($submissionIds) {
                $answers = $item->answers()->whereIn('submission_id', $submissionIds)->get();

                return [
                    'kategori_kode' => $item->kategori_kode,
                    'kategori' => $item->kategori,
                    'nomor' => $item->nomor,
                    'pertanyaan' => $item->pertanyaan,
                    'ya' => $answers->where('jawaban', 'ya')->count(),
                    'tidak' => $answers->where('jawaban', 'tidak')->count(),
                    'total_isi' => $answers->count(),
                ];
            });

        $obsSubmissionIds = ObservationSubmission::where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)->pluck('id');

        $observationRecap = ObservationItem::orderBy('kategori_kode')->orderBy('nomor')->get()
            ->map(function (ObservationItem $item) use ($obsSubmissionIds) {
                $answers = $item->answers()->whereIn('submission_id', $obsSubmissionIds)->get();

                return [
                    'kategori_kode' => $item->kategori_kode,
                    'kategori' => $item->kategori,
                    'nomor' => $item->nomor,
                    'item_teks' => $item->item_teks,
                    'skala_kondisi' => $item->skala_kondisi,
                    'ada' => $answers->where('ada', 'ada')->count(),
                    'tidak_ada' => $answers->where('ada', 'tidak_ada')->count(),
                    'kondisi' => $answers->whereNotNull('kondisi')->countBy('kondisi'),
                    'total_isi' => $answers->count(),
                ];
            });

        return response()->json([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'total_puskesmas' => Puskesmas::count(),
            'jumlah_puskesmas_isi_kuesioner' => $submissionIds->count(),
            'jumlah_puskesmas_isi_observasi' => $obsSubmissionIds->count(),
            'kuesioner' => $questionnaireRecap,
            'observasi' => $observationRecap,
        ]);
    }
}
