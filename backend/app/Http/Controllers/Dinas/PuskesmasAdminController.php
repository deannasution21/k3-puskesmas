<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetPasswordRequest;
use App\Http\Requests\Admin\StorePuskesmasRequest;
use App\Http\Requests\Admin\UpdatePuskesmasRequest;
use App\Models\Puskesmas;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PuskesmasAdminController extends Controller
{
    public function index()
    {
        return Puskesmas::with('user:id,username,puskesmas_id')->orderBy('nama')->get();
    }

    public function store(StorePuskesmasRequest $request)
    {
        $data = $request->validated();

        $puskesmas = DB::transaction(function () use ($data) {
            $puskesmas = Puskesmas::create([
                'nama' => $data['nama'],
                'alamat' => $data['alamat'] ?? null,
                'kepala_puskesmas' => $data['kepala_puskesmas'] ?? null,
                'no_hp' => $data['no_hp'] ?? null,
                'email' => $data['email'] ?? null,
                'kode_puskesmas' => $data['kode_puskesmas'] ?? null,
            ]);

            User::create([
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'role' => 'puskesmas',
                'puskesmas_id' => $puskesmas->id,
            ]);

            return $puskesmas;
        });

        return response()->json($puskesmas->load('user:id,username,puskesmas_id'), 201);
    }

    public function update(UpdatePuskesmasRequest $request, Puskesmas $puskesmas)
    {
        $data = $request->validated();

        $puskesmas->update([
            'nama' => $data['nama'],
            'alamat' => $data['alamat'] ?? null,
            'kepala_puskesmas' => $data['kepala_puskesmas'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'email' => $data['email'] ?? null,
            'kode_puskesmas' => $data['kode_puskesmas'] ?? null,
        ]);

        $puskesmas->user?->update(['username' => $data['username']]);

        return response()->json($puskesmas->load('user:id,username,puskesmas_id'));
    }

    public function destroy(Puskesmas $puskesmas)
    {
        $puskesmas->delete();

        return response()->noContent();
    }

    public function resetPassword(ResetPasswordRequest $request, Puskesmas $puskesmas)
    {
        abort_if(! $puskesmas->user, 404, 'Puskesmas ini belum memiliki akun.');

        $puskesmas->user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->noContent();
    }
}
