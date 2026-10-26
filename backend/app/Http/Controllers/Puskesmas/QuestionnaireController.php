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

    public function reusableAttachments(Request $request, QuestionnaireItem $item)
    {
        $user = $request->user();
        $bulan = Periode::bulanSekarang();
        $tahun = Periode::tahunSekarang();
        $currentSubmissionId = QuestionnaireSubmission::where('puskesmas_id', $user->puskesmas_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->value('id');
        $alreadyReusedIds = $currentSubmissionId
            ? QuestionnaireAnswerAttachment::where('submission_id', $currentSubmissionId)
                ->where('item_id', $item->id)
                ->whereNotNull('reused_from_attachment_id')
                ->pluck('reused_from_attachment_id')
            : collect();

        $attachments = QuestionnaireAnswerAttachment::with('submission:id,periode_bulan,periode_tahun')
            ->where('item_id', $item->id)
            ->when($alreadyReusedIds->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $alreadyReusedIds))
            ->whereHas('submission', function ($query) use ($user, $bulan, $tahun) {
                $query->where('puskesmas_id', $user->puskesmas_id)
                    ->where(function ($query) use ($bulan, $tahun) {
                        $query->where('periode_tahun', '<', $tahun)
                            ->orWhere(function ($query) use ($bulan, $tahun) {
                                $query->where('periode_tahun', $tahun)
                                    ->where('periode_bulan', '<', $bulan);
                            });
                    });
            })
            ->orderByDesc('id')
            ->get()
            ->filter(fn (QuestionnaireAnswerAttachment $attachment) => Storage::disk('local')->exists($attachment->path))
            ->values()
            ->map(fn (QuestionnaireAnswerAttachment $attachment) => [
                'id' => $attachment->id,
                'item_id' => $attachment->item_id,
                'original_name' => $attachment->original_name,
                'mime_type' => $attachment->mime_type,
                'size' => $attachment->size,
                'created_at' => $attachment->created_at,
                'periode_bulan' => $attachment->submission->periode_bulan,
                'periode_tahun' => $attachment->submission->periode_tahun,
            ]);

        return response()->json($attachments);
    }

    public function reuseAttachment(Request $request, QuestionnaireItem $item)
    {
        $validated = $request->validate([
            'attachment_id' => ['required', 'integer'],
            'confirmed' => ['required', 'accepted'],
        ]);

        $user = $request->user();
        $bulan = Periode::bulanSekarang();
        $tahun = Periode::tahunSekarang();

        $source = QuestionnaireAnswerAttachment::with('submission')
            ->whereKey($validated['attachment_id'])
            ->where('item_id', $item->id)
            ->whereHas('submission', function ($query) use ($user, $bulan, $tahun) {
                $query->where('puskesmas_id', $user->puskesmas_id)
                    ->where(function ($query) use ($bulan, $tahun) {
                        $query->where('periode_tahun', '<', $tahun)
                            ->orWhere(function ($query) use ($bulan, $tahun) {
                                $query->where('periode_tahun', $tahun)
                                    ->where('periode_bulan', '<', $bulan);
                            });
                    });
            })
            ->firstOrFail();

        abort_unless(Storage::disk('local')->exists($source->path), 404, 'Berkas sumber tidak tersedia.');

        $submission = QuestionnaireSubmission::firstOrCreate(
            [
                'puskesmas_id' => $user->puskesmas_id,
                'periode_bulan' => $bulan,
                'periode_tahun' => $tahun,
            ],
            ['user_id' => $user->id],
        );

        $existingReuse = $submission->attachments()
            ->where('item_id', $item->id)
            ->where('reused_from_attachment_id', $source->id)
            ->exists();

        if ($existingReuse) {
            return response()->json(['message' => 'Bukti tersebut sudah digunakan pada periode ini.'], 409);
        }

        $attachment = $submission->attachments()->create([
            'item_id' => $item->id,
            'uploaded_by' => $user->id,
            'original_name' => $source->original_name,
            'path' => $source->path,
            'mime_type' => $source->mime_type,
            'size' => $source->size,
            'reused_from_attachment_id' => $source->id,
            'reused_from_periode_bulan' => $source->submission->periode_bulan,
            'reused_from_periode_tahun' => $source->submission->periode_tahun,
            'reuse_confirmed_by' => $user->id,
            'reuse_confirmed_at' => now(),
        ]);

        return response()->json($attachment, 201);
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

        $path = $attachment->path;
        $attachment->delete();

        if (! QuestionnaireAnswerAttachment::where('path', $path)->exists()) {
            Storage::disk('local')->delete($path);
        }

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
