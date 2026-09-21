<?php

namespace App\Http\Requests\Questionnaire;

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
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.item_id' => ['required', 'integer', 'exists:questionnaire_items,id'],
            'answers.*.jawaban' => ['required', 'in:ya,tidak'],
        ];
    }
}
