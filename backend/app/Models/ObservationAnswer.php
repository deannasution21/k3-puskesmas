<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['submission_id', 'item_id', 'ada', 'kondisi', 'keterangan'])]
class ObservationAnswer extends Model
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
