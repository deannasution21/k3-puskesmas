<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['puskesmas_id', 'user_id', 'periode_bulan', 'periode_tahun', 'tanggal_observasi'])]
class ObservationSubmission extends Model
{
    protected function casts(): array
    {
        return [
            'tanggal_observasi' => 'date',
        ];
    }

    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ObservationAnswer::class, 'submission_id');
    }
}
