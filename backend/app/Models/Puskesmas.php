<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['nama', 'alamat'])]
class Puskesmas extends Model
{
    use HasFactory;

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function questionnaireSubmissions(): HasMany
    {
        return $this->hasMany(QuestionnaireSubmission::class);
    }

    public function observationSubmissions(): HasMany
    {
        return $this->hasMany(ObservationSubmission::class);
    }
}
