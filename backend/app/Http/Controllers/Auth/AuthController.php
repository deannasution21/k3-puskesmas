<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'username' => 'Username atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json($this->formatUser($request->user()));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    private function formatUser($user): array
    {
        $user->loadMissing('puskesmas');

        return [
            'id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'puskesmas' => $user->puskesmas ? [
                'id' => $user->puskesmas->id,
                'nama' => $user->puskesmas->nama,
            ] : null,
        ];
    }
}
