<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'submission_id',
    'item_id',
    'uploaded_by',
    'original_name',
    'path',
    'mime_type',
    'size',
    'reused_from_attachment_id',
    'reused_from_periode_bulan',
    'reused_from_periode_tahun',
    'reuse_confirmed_by',
    'reuse_confirmed_at',
])]
class QuestionnaireAnswerAttachment extends Model
{
    protected function casts(): array
    {
        return [
            'reuse_confirmed_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireSubmission::class, 'submission_id');
    }

    public function reusedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reused_from_attachment_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireItem::class, 'item_id');
    }
}
