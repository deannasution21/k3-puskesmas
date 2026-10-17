<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['submission_id', 'item_id', 'uploaded_by', 'original_name', 'path', 'mime_type', 'size'])]
class ObservationAnswerAttachment extends Model
{
    public function submission(): BelongsTo
    {
        return $this->belongsTo(ObservationSubmission::class, 'submission_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ObservationItem::class, 'item_id');
    }
}
