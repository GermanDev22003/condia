<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    /** Lista de personal (para asignar solicitudes). Filtros: ?cargo=mantenimiento&estado=activo */
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
}
