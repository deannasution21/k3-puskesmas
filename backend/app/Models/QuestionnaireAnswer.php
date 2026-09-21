<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['submission_id', 'item_id', 'jawaban'])]
class QuestionnaireAnswer extends Model
{
    public function submission(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireSubmission::class, 'submission_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireItem::class, 'item_id');
    }
}
