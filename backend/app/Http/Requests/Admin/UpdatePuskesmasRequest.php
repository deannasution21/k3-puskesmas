<?php

namespace App\Http\Requests\Admin;

use App\Models\Puskesmas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePuskesmasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Puskesmas $puskesmas */
        $puskesmas = $this->route('puskesmas');

        return [
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'kepala_puskesmas' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'kode_puskesmas' => [
                'nullable', 'string', 'max:50',
                Rule::unique('puskesmas', 'kode_puskesmas')->ignore($puskesmas->id),
            ],
            'username' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($puskesmas->user?->id),
            ],
        ];
    }
}
