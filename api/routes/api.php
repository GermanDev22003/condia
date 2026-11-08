<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\UnidadController;
use Illuminate\Support\Facades\Route;

// Pública (máximo 10 intentos por minuto)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Requieren token
Route::middleware('auth.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Solo administrador
    Route::middleware('admin')->group(function () {
        Route::get('/unidades', [UnidadController::class, 'index']);
        Route::apiResource('residentes', ResidenteController::class);
    });
});
