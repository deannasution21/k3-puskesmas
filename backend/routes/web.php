<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Fallback: melayani SPA Vue (public/spa/index.html) untuk semua request non-API,
// termasuk deep link seperti /dinas/dashboard. Route ini hanya jalan kalau tidak ada
// route web/api lain yang cocok, jadi tidak mengganggu /api/* maupun /sanctum/*.
Route::fallback(function (Request $request) {
    if ($request->is('api/*') || $request->is('sanctum/*')) {
        return response()->json(['message' => 'Not Found.'], 404);
    }

    $spaIndex = public_path('spa/index.html');

    if (! file_exists($spaIndex)) {
        return view('welcome');
    }

    return response()->file($spaIndex, ['Content-Type' => 'text/html']);
});
