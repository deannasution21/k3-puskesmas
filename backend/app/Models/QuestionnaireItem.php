<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kategori_kode', 'kategori', 'nomor', 'pertanyaan', 'rujukan'])]
class QuestionnaireItem extends Model
{
    public $timestamps = true;

    public function answers(): HasMany
    {
        return $this->hasMany(QuestionnaireAnswer::class, 'item_id');
    }
}
