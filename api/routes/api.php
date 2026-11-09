<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CuotaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\MisCuotasController;
use App\Http\Controllers\MisReportesController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\QuejaController;
use App\Http\Controllers\SolicitudMantenimientoController;
use App\Http\Controllers\ResidenteController;
use App\Http\Controllers\UnidadController;
use Illuminate\Support\Facades\Route;

// Pública (máximo 10 intentos por minuto)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

// Requieren token
Route::middleware('auth.token')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/cambiar-password', [AuthController::class, 'cambiarPassword'])->middleware('throttle:10,1');

    // Solo administrador
    Route::middleware('admin')->group(function () {
        Route::get('/unidades', [UnidadController::class, 'index']);
        Route::apiResource('residentes', ResidenteController::class);

        Route::get('/cuotas', [CuotaController::class, 'index']);
        Route::post('/cuotas', [CuotaController::class, 'store']);
        Route::get('/cuotas/{cuota}', [CuotaController::class, 'show']);
        Route::post('/cuotas/{cuota}/pagos', [CuotaController::class, 'registrarPago']);
        Route::get('/ingresos', [IngresoController::class, 'index']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/quejas', [QuejaController::class, 'index']);
        Route::put('/quejas/{queja}', [QuejaController::class, 'update']);
        Route::get('/solicitudes-mantenimiento', [SolicitudMantenimientoController::class, 'index']);
        Route::put('/solicitudes-mantenimiento/{solicitud}', [SolicitudMantenimientoController::class, 'update']);
        Route::apiResource('personal', PersonalController::class)->parameters(['personal' => 'personal']);
    });

    // Solo residente
    Route::middleware('residente')->group(function () {
        Route::get('/mis-cuotas', [MisCuotasController::class, 'index']);
        Route::get('/mis-cuotas/{id}', [MisCuotasController::class, 'show']);
        Route::get('/mis-quejas', [MisReportesController::class, 'quejas']);
        Route::post('/mis-quejas', [MisReportesController::class, 'crearQueja']);
        Route::get('/mis-solicitudes', [MisReportesController::class, 'solicitudes']);
        Route::post('/mis-solicitudes', [MisReportesController::class, 'crearSolicitud']);
    });
});
