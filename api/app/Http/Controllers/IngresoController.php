<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IngresoController extends Controller
{
    /** Ingresos (pagos registrados) en un rango de fechas. */
    public function index(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ], ['hasta.after_or_equal' => 'La fecha final no puede ser menor que la inicial']);

        $pagos = Pago::with('cuota:id_cuota,id_residente,concepto,periodo', 'cuota.residente:id_residente,nombres,apellidos')
            ->whereBetween('fecha_pago', [$datos['desde'], $datos['hasta']])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id_pago')
            ->get();

        return response()->json([
            'desde' => $datos['desde'],
            'hasta' => $datos['hasta'],
            'total' => number_format((float) $pagos->sum('monto_pagado'), 2, '.', ''),
            'cantidad_pagos' => $pagos->count(),
            'pagos' => $pagos,
        ]);
    }
}
