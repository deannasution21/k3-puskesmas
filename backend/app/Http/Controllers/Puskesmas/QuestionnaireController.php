<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Questionnaire\StoreSubmissionRequest;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSubmission;
use App\Support\Periode;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function items()
    {
        return QuestionnaireItem::orderBy('kategori_kode')->orderBy('nomor')->get();
    }

    public function current(Request $request)
    {
        $puskesmasId = $request->user()->puskesmas_id;

        $submission = QuestionnaireSubmission::with('answers')
            ->where('puskesmas_id', $puskesmasId)
            ->where('periode_bulan', Periode::bulanSekarang())
            ->where('periode_tahun', Periode::tahunSekarang())
            ->first();

        return response()->json([
            'periode_bulan' => Periode::bulanSekarang(),
            'periode_tahun' => Periode::tahunSekarang(),
            'submission' => $submission,
        ]);
    }

    public function store(StoreSubmissionRequest $request)
    {
        $user = $request->user();

        $submission = QuestionnaireSubmission::firstOrCreate(
            [
                'puskesmas_id' => $user->puskesmas_id,
                'periode_bulan' => Periode::bulanSekarang(),
                'periode_tahun' => Periode::tahunSekarang(),
            ],
            ['user_id' => $user->id],
        );

        $submission->update(['user_id' => $user->id]);

        foreach ($request->validated('answers') as $answer) {
            $submission->answers()->updateOrCreate(
                ['item_id' => $answer['item_id']],
                ['jawaban' => $answer['jawaban']],
            );
        }

        return response()->json($submission->load('answers'));
    }

    public function history(Request $request)
    {
        return QuestionnaireSubmission::where('puskesmas_id', $request->user()->puskesmas_id)
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->withCount('answers')
            ->get();
    }

    public function show(Request $request, string $periode)
    {
        [$bulan, $tahun] = Periode::parse($periode);

        $submission = QuestionnaireSubmission::with('answers.item')
            ->where('puskesmas_id', $request->user()->puskesmas_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->firstOrFail();

        return response()->json($submission);
    }
}
