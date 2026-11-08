<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Pago;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CuotaController extends Controller
{
    private const RELACIONES = ['residente:id_residente,id_unidad,nombres,apellidos', 'residente.unidad:id_unidad,numero'];

    public function index(Request $request): JsonResponse
    {
        Cuota::marcarVencidas();

        $consulta = Cuota::with(self::RELACIONES)
            ->withSum('pagos as total_pagado', 'monto_pagado')
            ->orderByDesc('fecha_vencimiento')
            ->orderByDesc('id_cuota');

        foreach (['estado', 'periodo', 'id_residente'] as $filtro) {
            if ($request->filled($filtro)) {
                $consulta->where($filtro, $request->query($filtro));
            }
        }

        return response()->json($consulta->paginate(15));
    }

    public function show(Cuota $cuota): JsonResponse
    {
        Cuota::marcarVencidas();
        $cuota->refresh();

        return response()->json($cuota->load(self::RELACIONES)->load('pagos')->setAttribute('total_pagado', $cuota->totalPagado()));
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_residente' => ['required', 'integer', 'exists:residentes,id_residente'],
            'concepto' => ['required', 'string', 'max:100'],
            'monto' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'periodo' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'fecha_vencimiento' => ['required', 'date'],
        ], ['periodo.regex' => 'El período debe tener el formato AAAA-MM (por ejemplo 2026-11)']);

        if (Residente::find($datos['id_residente'])->estado !== 'activo') {
            return response()->json([
                'mensaje' => 'Los datos no son válidos',
                'errors' => ['id_residente' => ['El residente está inactivo']],
            ], 422);
        }

        $existe = Cuota::where('id_residente', $datos['id_residente'])
            ->where('concepto', $datos['concepto'])
            ->where('periodo', $datos['periodo'])
            ->exists();

        if ($existe) {
            return response()->json([
                'mensaje' => 'Los datos no son válidos',
                'errors' => ['concepto' => ['Ya existe una cuota con ese concepto y período para este residente']],
            ], 422);
        }

        $cuota = Cuota::create($datos + ['estado' => 'pendiente']);

        return response()->json($cuota->load(self::RELACIONES), 201);
    }

    /** Registra un pago (total o parcial) de una cuota. */
    public function registrarPago(Request $request, Cuota $cuota): JsonResponse
    {
        $datos = $request->validate([
            'monto_pagado' => ['required', 'numeric', 'gt:0'],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'metodo_pago' => ['required', Rule::in(['efectivo', 'transferencia', 'cheque'])],
            'referencia' => ['nullable', 'string', 'max:50'],
        ]);

        $resultado = DB::transaction(function () use ($cuota, $datos, $request) {
            $cuota = Cuota::lockForUpdate()->find($cuota->id_cuota);

            if ($cuota->estado === 'pagada') {
                return ['error' => 'La cuota ya está pagada'];
            }

            $saldo = round((float) $cuota->monto - (float) $cuota->pagos()->sum('monto_pagado'), 2);

            if (round((float) $datos['monto_pagado'], 2) > $saldo) {
                return ['error' => 'El monto supera el saldo pendiente ($'.number_format($saldo, 2).')'];
            }

            $pago = Pago::create($datos + [
                'id_cuota' => $cuota->id_cuota,
                'id_usuario_registro' => $request->user()->id_usuario,
            ]);

            if (round((float) $datos['monto_pagado'], 2) === $saldo) {
                $cuota->update(['estado' => 'pagada']);
            }

            return ['pago' => $pago, 'cuota' => $cuota->fresh()];
        });

        if (isset($resultado['error'])) {
            return response()->json([
                'mensaje' => 'Los datos no son válidos',
                'errors' => ['monto_pagado' => [$resultado['error']]],
            ], 422);
        }

        return response()->json([
            'mensaje' => $resultado['cuota']->estado === 'pagada' ? 'Pago registrado, la cuota quedó pagada' : 'Pago parcial registrado',
            'pago' => $resultado['pago'],
            'cuota' => $resultado['cuota']->setAttribute('total_pagado', $resultado['cuota']->totalPagado()),
        ], 201);
    }
}
