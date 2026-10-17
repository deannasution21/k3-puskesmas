<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\ObservationAnswer;
use App\Models\ObservationAnswerAttachment;
use App\Models\ObservationItem;
use App\Models\ObservationSubmission;
use App\Models\Puskesmas;
use App\Models\QuestionnaireAnswer;
use App\Models\QuestionnaireAnswerAttachment;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSubmission;
use App\Support\Penilaian;
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
            'penilaian' => Penilaian::ringkasan($bulan, $tahun),
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

        $penilaian = Penilaian::untukSemuaPuskesmas($bulan, $tahun)->keyBy('puskesmas_id');

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
            'penilaian_persen' => $penilaian->get($p->id)['persen'] ?? 0,
            'penilaian_kategori_key' => $penilaian->get($p->id)['kategori_key'] ?? 'tidak_memenuhi',
            'penilaian_kategori_label' => $penilaian->get($p->id)['kategori_label'] ?? 'Tidak Memenuhi',
        ]);

        return response()->json([
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'data' => $puskesmas,
        ]);
    }

    public function puskesmasShow(Puskesmas $puskesmas)
    {
        $kuesionerHistory = $puskesmas->questionnaireSubmissions()
            ->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')
            ->withCount('answers')->get();

        $observasiHistory = $puskesmas->observationSubmissions()
            ->orderByDesc('periode_tahun')->orderByDesc('periode_bulan')
            ->withCount('answers')->get();

        $periods = $kuesionerHistory->map(fn ($s) => ['bulan' => $s->periode_bulan, 'tahun' => $s->periode_tahun])
            ->concat($observasiHistory->map(fn ($s) => ['bulan' => $s->periode_bulan, 'tahun' => $s->periode_tahun]))
            ->unique(fn ($p) => "{$p['tahun']}-{$p['bulan']}")
            ->sortByDesc(fn ($p) => $p['tahun'] * 100 + $p['bulan'])
            ->values();

        $riwayat = $periods->map(function (array $p) use ($puskesmas, $kuesionerHistory, $observasiHistory) {
            $kuesioner = $kuesionerHistory->first(fn ($s) => $s->periode_bulan === $p['bulan'] && $s->periode_tahun === $p['tahun']);
            $observasi = $observasiHistory->first(fn ($s) => $s->periode_bulan === $p['bulan'] && $s->periode_tahun === $p['tahun']);

            return [
                'periode_bulan' => $p['bulan'],
                'periode_tahun' => $p['tahun'],
                'kuesioner_answers_count' => $kuesioner?->answers_count,
                'observasi_answers_count' => $observasi?->answers_count,
                'penilaian' => Penilaian::untukPuskesmas($puskesmas->id, $p['bulan'], $p['tahun']),
            ];
        });

        return response()->json([
            'puskesmas' => $puskesmas->load('user:id,username,puskesmas_id'),
            'riwayat' => $riwayat,
        ]);
    }

    public function questionnaireDetail(Puskesmas $puskesmas, string $periode)
    {
        [$bulan, $tahun] = Periode::parse($periode);

        $submission = $puskesmas->questionnaireSubmissions()
            ->with(['answers.item', 'attachments'])
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->firstOrFail();

        return response()->json($submission);
    }

    public function observationDetail(Puskesmas $puskesmas, string $periode)
    {
        [$bulan, $tahun] = Periode::parse($periode);

        $submission = $puskesmas->observationSubmissions()
            ->with(['answers.item', 'attachments'])
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

        // Item ditampilkan kalau masih aktif (untuk periode berjalan/depan), ATAU
        // punya jawaban di periode yang dipilih (supaya item yang sudah dinonaktifkan
        // tetap muncul saat melihat rekap periode lama saat item itu masih berlaku).
        $questionnaireRecap = QuestionnaireItem::where(function ($query) use ($submissionIds) {
            $query->where('aktif', true)
                ->orWhereHas('answers', fn ($q) => $q->whereIn('submission_id', $submissionIds));
        })
            ->orderBy('kategori_kode')->orderBy('nomor')->get()
            ->map(function (QuestionnaireItem $item) use ($submissionIds) {
                $answers = $item->answers()->whereIn('submission_id', $submissionIds)->get();

                return [
                    'id' => $item->id,
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

        $observationRecap = ObservationItem::where(function ($query) use ($obsSubmissionIds) {
            $query->where('aktif', true)
                ->orWhereHas('answers', fn ($q) => $q->whereIn('submission_id', $obsSubmissionIds));
        })
            ->orderBy('kategori_kode')->orderBy('nomor')->get()
            ->map(function (ObservationItem $item) use ($obsSubmissionIds) {
                $answers = $item->answers()->whereIn('submission_id', $obsSubmissionIds)->get();

                return [
                    'id' => $item->id,
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

    public function recapKuesionerItemPuskesmas(Request $request, QuestionnaireItem $item)
    {
        $bulan = (int) $request->query('bulan', Periode::bulanSekarang());
        $tahun = (int) $request->query('tahun', Periode::tahunSekarang());

        $answers = QuestionnaireAnswer::where('item_id', $item->id)
            ->whereHas('submission', fn ($q) => $q->where('periode_bulan', $bulan)->where('periode_tahun', $tahun))
            ->with('submission:id,puskesmas_id')
            ->get()
            ->keyBy(fn (QuestionnaireAnswer $a) => $a->submission->puskesmas_id);

        $attachments = QuestionnaireAnswerAttachment::where('item_id', $item->id)
            ->whereHas('submission', fn ($q) => $q->where('periode_bulan', $bulan)->where('periode_tahun', $tahun))
            ->with('submission:id,puskesmas_id')
            ->get()
            ->groupBy(fn (QuestionnaireAnswerAttachment $a) => $a->submission->puskesmas_id);

        $data = Puskesmas::orderBy('nama')->get(['id', 'nama', 'kepala_puskesmas'])
            ->map(function (Puskesmas $p) use ($answers, $attachments) {
                $answer = $answers->get($p->id);

                return [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'kepala_puskesmas' => $p->kepala_puskesmas,
                    'jawaban' => $answer->jawaban ?? null,
                    'lampiran' => ($attachments->get($p->id) ?? collect())->map(fn (QuestionnaireAnswerAttachment $a) => [
                        'id' => $a->id,
                        'original_name' => $a->original_name,
                        'mime_type' => $a->mime_type,
                        'size' => $a->size,
                    ])->values(),
                ];
            });

        return response()->json([
            'item' => ['id' => $item->id, 'nomor' => $item->nomor, 'pertanyaan' => $item->pertanyaan],
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'data' => $data,
        ]);
    }

    public function recapObservasiItemPuskesmas(Request $request, ObservationItem $item)
    {
        $bulan = (int) $request->query('bulan', Periode::bulanSekarang());
        $tahun = (int) $request->query('tahun', Periode::tahunSekarang());

        $answers = ObservationAnswer::where('item_id', $item->id)
            ->whereHas('submission', fn ($q) => $q->where('periode_bulan', $bulan)->where('periode_tahun', $tahun))
            ->with('submission:id,puskesmas_id')
            ->get()
            ->keyBy(fn (ObservationAnswer $a) => $a->submission->puskesmas_id);

        $attachments = ObservationAnswerAttachment::where('item_id', $item->id)
            ->whereHas('submission', fn ($q) => $q->where('periode_bulan', $bulan)->where('periode_tahun', $tahun))
            ->with('submission:id,puskesmas_id')
            ->get()
            ->groupBy(fn (ObservationAnswerAttachment $a) => $a->submission->puskesmas_id);

        $data = Puskesmas::orderBy('nama')->get(['id', 'nama', 'kepala_puskesmas'])
            ->map(function (Puskesmas $p) use ($answers, $attachments) {
                $answer = $answers->get($p->id);

                return [
                    'id' => $p->id,
                    'nama' => $p->nama,
                    'kepala_puskesmas' => $p->kepala_puskesmas,
                    'ada' => $answer->ada ?? null,
                    'kondisi' => $answer->kondisi ?? null,
                    'lampiran' => ($attachments->get($p->id) ?? collect())->map(fn (ObservationAnswerAttachment $a) => [
                        'id' => $a->id,
                        'original_name' => $a->original_name,
                        'mime_type' => $a->mime_type,
                        'size' => $a->size,
                    ])->values(),
                ];
            });

        return response()->json([
            'item' => ['id' => $item->id, 'nomor' => $item->nomor, 'item_teks' => $item->item_teks],
            'periode_bulan' => $bulan,
            'periode_tahun' => $tahun,
            'data' => $data,
        ]);
    }
}
