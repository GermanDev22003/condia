<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Queja;
use App\Models\Residente;
use App\Models\SolicitudMantenimiento;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /** Resumen para la pantalla de inicio del administrador. */
    public function index(): JsonResponse
    {
        Cuota::marcarVencidas();

        $inicioMes = now()->startOfMonth()->toDateString();
        $finMes = now()->endOfMonth()->toDateString();

        $cuotasConSaldo = Cuota::whereIn('estado', ['pendiente', 'vencida'])->withSum('pagos', 'monto_pagado')->get();
        $porCobrar = $cuotasConSaldo->sum(fn ($c) => (float) $c->monto - (float) ($c->pagos_sum_monto_pagado ?? 0));

        return response()->json([
            'residentes_activos' => Residente::where('estado', 'activo')->count(),
            'ingresos_mes' => round((float) Pago::whereBetween('fecha_pago', [$inicioMes, $finMes])->sum('monto_pagado'), 2),
            'por_cobrar' => round($porCobrar, 2),
            'cuotas_vencidas' => Cuota::where('estado', 'vencida')->count(),
            'quejas_abiertas' => Queja::whereIn('estado', ['pendiente', 'en_proceso'])->count(),
            'mantenimiento_abierto' => SolicitudMantenimiento::whereIn('estado', ['pendiente', 'en_proceso'])->count(),
            'quejas_recientes' => Queja::with('residente:id_residente,nombres,apellidos')
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->orderByDesc('fecha_registro')->limit(5)->get(['id_queja', 'id_residente', 'asunto', 'estado', 'fecha_registro']),
            'solicitudes_recientes' => SolicitudMantenimiento::with('residente:id_residente,nombres,apellidos')
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->orderByDesc('fecha_registro')->limit(5)->get(['id_solicitud', 'id_residente', 'titulo', 'prioridad', 'estado', 'fecha_registro']),
        ]);
    }
}
