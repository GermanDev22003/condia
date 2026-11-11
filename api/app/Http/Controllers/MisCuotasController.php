<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Cuotas y pagos del residente que inició sesión (solo lectura). */
class MisCuotasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $residente = Residente::where('id_usuario', $request->user()->id_usuario)->firstOrFail();

        Cuota::marcarVencidas();

        $cuotas = Cuota::where('id_residente', $residente->id_residente)
            ->with('pagos')
            ->withSum('pagos as total_pagado', 'monto_pagado')
            ->orderByDesc('fecha_vencimiento')
            ->get();

        return response()->json([
            'pendiente_total' => number_format(
                (float) $cuotas->whereIn('estado', ['pendiente', 'vencida'])->sum(fn ($c) => (float) $c->monto - (float) $c->total_pagado),
                2, '.', ''
            ),
            'cuotas' => $cuotas,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $residente = Residente::where('id_usuario', $request->user()->id_usuario)->firstOrFail();
        Cuota::marcarVencidas();

        $cuota = Cuota::where('id_residente', $residente->id_residente)->with('pagos')->findOrFail($id);

        return response()->json($cuota->setAttribute('total_pagado', $cuota->totalPagado()));
    }
}
