<?php

namespace App\Http\Controllers\Puskesmas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Observation\StoreAttachmentRequest;
use App\Http\Requests\Observation\StoreSubmissionRequest;
use App\Models\ObservationAnswerAttachment;
use App\Models\ObservationItem;
use App\Models\ObservationSubmission;
use App\Support\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ObservationController extends Controller
{
    private const SKALA_OPTIONS = [
        'baik_buruk' => ['baik', 'buruk'],
        'baik_rusak_ringan_rusak_berat' => ['baik', 'rusak_ringan', 'rusak_berat'],
    ];

    public function items()
    {
        return ObservationItem::where('aktif', true)->orderBy('kategori_kode')->orderBy('nomor')->get();
    }

    public function current(Request $request)
    {
        $puskesmasId = $request->user()->puskesmas_id;

        $submission = ObservationSubmission::with(['answers', 'attachments'])
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
        $answers = $request->validated('answers');

        $items = ObservationItem::whereIn('id', collect($answers)->pluck('item_id'))
            ->get()->keyBy('id');

        foreach ($answers as $answer) {
            $item = $items->get($answer['item_id']);
            $kondisi = $answer['kondisi'] ?? null;

            if ($kondisi !== null && ! in_array($kondisi, self::SKALA_OPTIONS[$item->skala_kondisi], true)) {
                throw ValidationException::withMessages([
                    'answers' => "Nilai kondisi '{$kondisi}' tidak sesuai skala item \"{$item->item_teks}\".",
                ]);
            }
        }

        $submission = ObservationSubmission::firstOrCreate(
            [
                'puskesmas_id' => $user->puskesmas_id,
                'periode_bulan' => Periode::bulanSekarang(),
                'periode_tahun' => Periode::tahunSekarang(),
            ],
            ['user_id' => $user->id],
        );

        $submission->update([
            'user_id' => $user->id,
            'tanggal_observasi' => $request->validated('tanggal_observasi') ?? $submission->tanggal_observasi,
        ]);

        foreach ($answers as $answer) {
            $submission->answers()->updateOrCreate(
                ['item_id' => $answer['item_id']],
                [
                    'ada' => $answer['ada'],
                    'kondisi' => $answer['kondisi'] ?? null,
                    'keterangan' => $answer['keterangan'] ?? null,
                ],
            );
        }

        return response()->json($submission->load('answers'));
    }

    public function history(Request $request)
    {
        return ObservationSubmission::where('puskesmas_id', $request->user()->puskesmas_id)
            ->orderByDesc('periode_tahun')
            ->orderByDesc('periode_bulan')
            ->withCount('answers')
            ->get();
    }

    public function show(Request $request, string $periode)
    {
        [$bulan, $tahun] = Periode::parse($periode);

        $submission = ObservationSubmission::with(['answers.item', 'attachments'])
            ->where('puskesmas_id', $request->user()->puskesmas_id)
            ->where('periode_bulan', $bulan)
            ->where('periode_tahun', $tahun)
            ->firstOrFail();

        return response()->json($submission);
    }

    public function uploadAttachment(StoreAttachmentRequest $request, ObservationItem $item)
    {
        $user = $request->user();

        $submission = ObservationSubmission::firstOrCreate(
            [
                'puskesmas_id' => $user->puskesmas_id,
                'periode_bulan' => Periode::bulanSekarang(),
                'periode_tahun' => Periode::tahunSekarang(),
            ],
            ['user_id' => $user->id],
        );

        $attachments = collect($request->file('files'))->map(function ($file) use ($submission, $item, $user) {
            $path = $file->store("observation-attachments/{$submission->puskesmas_id}/{$submission->id}/{$item->id}", 'local');

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

    public function destroyAttachment(Request $request, ObservationAnswerAttachment $attachment)
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

    public function downloadAttachment(Request $request, ObservationAnswerAttachment $attachment)
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
