<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Questionnaire\StoreAttachmentRequest;
use App\Http\Requests\Questionnaire\StoreSubmissionRequest;
use App\Models\QuestionnaireAnswerAttachment;
use App\Models\QuestionnaireItem;
use App\Models\QuestionnaireSubmission;
use App\Support\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuestionnaireController extends Controller
{
    public function items()
    {
        return QuestionnaireItem::where('aktif', true)->orderBy('kategori_kode')->orderBy('nomor')->get();
    }

    public function current(Request $request)
    {
        $puskesmasId = $request->user()->puskesmas_id;

        $submission = QuestionnaireSubmission::with(['answers', 'attachments'])
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

        $submission = QuestionnaireSubmission::with(['answers.item', 'attachments'])
            ->where('puskesmas_id', $request->user()->puskesmas_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->firstOrFail();

        return response()->json($submission);
    }

    public function uploadAttachment(StoreAttachmentRequest $request, QuestionnaireItem $item)
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

        $attachments = collect($request->file('files'))->map(function ($file) use ($submission, $item, $user) {
            $path = $file->store("questionnaire-attachments/{$submission->puskesmas_id}/{$submission->id}/{$item->id}", 'local');

            return $submission->attachments()->create([
                'item_id' => $item->id,
                'uploaded_by' => $user->id,
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        });

        return response()->json($attachments->values());
    }

    public function destroyAttachment(Request $request, QuestionnaireAnswerAttachment $attachment)
    {
        $user = $request->user();

        abort_unless($attachment->submission->puskesmas_id === $user->puskesmas_id, 403);
        abort_unless(
            $attachment->submission->periode_bulan === Periode::bulanSekarang()
                && $attachment->submission->periode_tahun === Periode::tahunSekarang(),
            403,
            'Lampiran periode lalu tidak dapat dihapus.',
        );

        Storage::disk('local')->delete($attachment->path);
        $attachment->delete();

        return response()->json(['message' => 'Lampiran dihapus.']);
    }

    public function downloadAttachment(Request $request, QuestionnaireAnswerAttachment $attachment)
    {
        $user = $request->user();

        if ($user->isPuskesmas() && $attachment->submission->puskesmas_id !== $user->puskesmas_id) {
            abort(403);
        }

        return Storage::disk('local')->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }
}
