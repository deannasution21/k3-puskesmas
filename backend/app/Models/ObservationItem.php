<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kategori_kode', 'kategori', 'nomor', 'item_teks', 'skala_kondisi', 'sumber'])]
class ObservationItem extends Model
{
    public function answers(): HasMany
    {
        return $this->hasMany(ObservationAnswer::class, 'item_id');
    }
}
