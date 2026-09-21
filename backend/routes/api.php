<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dinas\DashboardController;
use App\Http\Controllers\Dinas\PuskesmasAdminController;
use App\Http\Controllers\Puskesmas\ObservationController;
use App\Http\Controllers\Puskesmas\QuestionnaireController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:puskesmas')->prefix('questionnaire')->group(function () {
        Route::get('/items', [QuestionnaireController::class, 'items']);
        Route::get('/submissions/current', [QuestionnaireController::class, 'current']);
        Route::post('/submissions', [QuestionnaireController::class, 'store']);
        Route::get('/submissions', [QuestionnaireController::class, 'history']);
        Route::get('/submissions/{periode}', [QuestionnaireController::class, 'show']);
    });

    Route::middleware('role:puskesmas')->prefix('observation')->group(function () {
        Route::get('/items', [ObservationController::class, 'items']);
        Route::get('/submissions/current', [ObservationController::class, 'current']);
        Route::post('/submissions', [ObservationController::class, 'store']);
        Route::get('/submissions', [ObservationController::class, 'history']);
        Route::get('/submissions/{periode}', [ObservationController::class, 'show']);
    });

    Route::middleware('role:dinas')->prefix('dashboard')->group(function () {
        Route::get('/summary', [DashboardController::class, 'summary']);
        Route::get('/puskesmas', [DashboardController::class, 'puskesmasIndex']);
        Route::get('/puskesmas/{puskesmas}', [DashboardController::class, 'puskesmasShow']);
        Route::get('/puskesmas/{puskesmas}/questionnaire/{periode}', [DashboardController::class, 'questionnaireDetail']);
        Route::get('/puskesmas/{puskesmas}/observation/{periode}', [DashboardController::class, 'observationDetail']);
        Route::get('/recap', [DashboardController::class, 'recap']);
    });

    Route::middleware('role:dinas')->prefix('admin/puskesmas')->group(function () {
        Route::get('/', [PuskesmasAdminController::class, 'index']);
        Route::post('/', [PuskesmasAdminController::class, 'store']);
        Route::put('/{puskesmas}', [PuskesmasAdminController::class, 'update']);
        Route::delete('/{puskesmas}', [PuskesmasAdminController::class, 'destroy']);
        Route::post('/{puskesmas}/reset-password', [PuskesmasAdminController::class, 'resetPassword']);
    });
});
