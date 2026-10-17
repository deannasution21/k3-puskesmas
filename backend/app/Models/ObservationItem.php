<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kategori_kode', 'kategori', 'nomor', 'item_teks', 'skala_kondisi', 'sumber', 'aktif'])]
class ObservationItem extends Model
{
    protected $casts = ['aktif' => 'boolean'];

    public function answers(): HasMany
    {
        return $this->hasMany(ObservationAnswer::class, 'item_id');
    }
}
