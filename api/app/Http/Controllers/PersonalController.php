<?php

namespace App\Http\Controllers;

use App\Models\Condominio;
use App\Models\Personal;
use App\Models\SolicitudMantenimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    /** Lista de personal. Filtros: ?cargo=mantenimiento&estado=activo */
    public function index(Request $request): JsonResponse
    {
        $consulta = Personal::orderBy('apellidos')->orderBy('nombres');

        foreach (['cargo', 'estado'] as $filtro) {
            if ($request->filled($filtro)) {
                $consulta->where($filtro, $request->query($filtro));
            }
        }

        return response()->json($consulta->get());
    }

    public function show(Personal $personal): JsonResponse
    {
        return response()->json($personal);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate($this->reglas());

        $personal = Personal::create($datos + [
            'id_condominio' => Condominio::orderBy('id_condominio')->value('id_condominio'),
            'estado' => $datos['estado'] ?? 'activo',
        ]);

        return response()->json($personal->fresh(), 201);
    }

    public function update(Request $request, Personal $personal): JsonResponse
    {
        $datos = $request->validate($this->reglas());

        if (($datos['estado'] ?? $personal->estado) === 'inactivo' && $personal->estado === 'activo') {
            if ($this->tieneSolicitudesEnProceso($personal)) {
                return $this->invalido('El empleado tiene solicitudes de mantenimiento en proceso; complétalas o reasígnalas antes de darlo de baja');
            }
        }

        $personal->update($datos);

        return response()->json($personal->fresh());
    }

    /** Baja lógica: el empleado queda inactivo. */
    public function destroy(Personal $personal): JsonResponse
    {
        if ($this->tieneSolicitudesEnProceso($personal)) {
            return $this->invalido('El empleado tiene solicitudes de mantenimiento en proceso; complétalas o reasígnalas antes de darlo de baja');
        }

        $personal->update(['estado' => 'inactivo']);

        return response()->json(['mensaje' => 'Empleado dado de baja']);
    }

    private function reglas(): array
    {
        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'cargo' => ['required', Rule::in(['mantenimiento', 'seguridad'])],
            'turno' => ['required', Rule::in(['diurno', 'nocturno', 'mixto'])],
            'telefono' => ['nullable', 'string', 'max:20'],
            'estado' => ['sometimes', Rule::in(['activo', 'inactivo'])],
        ];
    }

    private function tieneSolicitudesEnProceso(Personal $personal): bool
    {
        return SolicitudMantenimiento::where('id_personal', $personal->id_personal)
            ->where('estado', 'en_proceso')
            ->exists();
    }

    private function invalido(string $mensaje): JsonResponse
    {
        return response()->json(['mensaje' => 'Los datos no son válidos', 'errors' => ['estado' => [$mensaje]]], 422);
    }
}
