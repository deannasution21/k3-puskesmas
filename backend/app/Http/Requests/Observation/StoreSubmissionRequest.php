<?php

namespace App\Http\Requests\Observation;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_observasi' => ['nullable', 'date'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.item_id' => ['required', 'integer', 'exists:observation_items,id'],
            'answers.*.ada' => ['required', 'in:ada,tidak_ada'],
            'answers.*.kondisi' => ['nullable', 'in:baik,buruk,rusak_ringan,rusak_berat'],
            'answers.*.keterangan' => ['nullable', 'string'],
        ];
    }
}
