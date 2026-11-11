<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\SolicitudMantenimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Atención de solicitudes de mantenimiento por el administrador. */
class SolicitudMantenimientoController extends Controller
{
    private const RELACIONES = [
        'residente:id_residente,id_unidad,nombres,apellidos',
        'residente.unidad:id_unidad,numero',
        'personal:id_personal,nombres,apellidos',
    ];

    public function index(Request $request): JsonResponse
    {
        $consulta = SolicitudMantenimiento::with(self::RELACIONES)
            ->orderByDesc('fecha_registro')
            ->orderByDesc('id_solicitud');

        if ($request->filled('estado')) {
            $consulta->where('estado', $request->query('estado'));
        }

        return response()->json($consulta->paginate(15));
    }

    public function update(Request $request, SolicitudMantenimiento $solicitud): JsonResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'en_proceso', 'completada'])],
            'id_personal' => ['nullable', 'integer', 'exists:personal,id_personal'],
        ]);

        if ($solicitud->estado === 'completada') {
            return $this->invalido('estado', 'La solicitud ya está completada y no se puede modificar');
        }

        $idPersonal = $datos['id_personal'] ?? $solicitud->id_personal;

        if ($idPersonal) {
            $empleado = Personal::find($idPersonal);
            if ($empleado->cargo !== 'mantenimiento' || $empleado->estado !== 'activo') {
                return $this->invalido('id_personal', 'Solo se puede asignar personal de mantenimiento activo');
            }
        }

        if (in_array($datos['estado'], ['en_proceso', 'completada'], true) && ! $idPersonal) {
            return $this->invalido('id_personal', 'Asigna un empleado de mantenimiento antes de avanzar la solicitud');
        }

        $solicitud->update(['estado' => $datos['estado'], 'id_personal' => $idPersonal]);

        return response()->json($solicitud->fresh()->load(self::RELACIONES));
    }

    private function invalido(string $campo, string $mensaje): JsonResponse
    {
        return response()->json(['mensaje' => 'Los datos no son válidos', 'errors' => [$campo => [$mensaje]]], 422);
    }
}
