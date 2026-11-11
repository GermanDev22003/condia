<?php

namespace App\Http\Controllers;

use App\Models\Queja;
use App\Models\Residente;
use App\Models\SolicitudMantenimiento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Quejas y solicitudes de mantenimiento del residente que inició sesión. */
class MisReportesController extends Controller
{
    private function residente(Request $request): Residente
    {
        return Residente::where('id_usuario', $request->user()->id_usuario)->firstOrFail();
    }

    public function quejas(Request $request): JsonResponse
    {
        return response()->json(
            Queja::where('id_residente', $this->residente($request)->id_residente)
                ->orderByDesc('fecha_registro')->orderByDesc('id_queja')->get()
        );
    }

    public function crearQueja(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'asunto' => ['required', 'string', 'max:150'],
            'categoria' => ['required', Rule::in(Queja::CATEGORIAS)],
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $queja = Queja::create($datos + ['id_residente' => $this->residente($request)->id_residente, 'estado' => 'pendiente']);

        return response()->json($queja->fresh(), 201);
    }

    public function solicitudes(Request $request): JsonResponse
    {
        return response()->json(
            SolicitudMantenimiento::with('personal:id_personal,nombres,apellidos')
                ->where('id_residente', $this->residente($request)->id_residente)
                ->orderByDesc('fecha_registro')->orderByDesc('id_solicitud')->get()
        );
    }

    public function crearSolicitud(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:150'],
            'ubicacion' => ['required', 'string', 'max:150'],
            'prioridad' => ['required', Rule::in(['baja', 'media', 'alta'])],
            'descripcion' => ['required', 'string', 'max:2000'],
        ]);

        $solicitud = SolicitudMantenimiento::create($datos + [
            'id_residente' => $this->residente($request)->id_residente,
            'estado' => 'pendiente',
        ]);

        return response()->json($solicitud->fresh(), 201);
    }
}
