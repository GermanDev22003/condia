<?php

namespace App\Http\Controllers;

use App\Models\Queja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Atención de quejas por el administrador. */
class QuejaController extends Controller
{
    private const RELACIONES = ['residente:id_residente,id_unidad,nombres,apellidos', 'residente.unidad:id_unidad,numero'];

    public function index(Request $request): JsonResponse
    {
        $consulta = Queja::with(self::RELACIONES)->orderByDesc('fecha_registro')->orderByDesc('id_queja');

        if ($request->filled('estado')) {
            $consulta->where('estado', $request->query('estado'));
        }

        return response()->json($consulta->paginate(15));
    }

    public function update(Request $request, Queja $queja): JsonResponse
    {
        $datos = $request->validate([
            'estado' => ['required', Rule::in(['pendiente', 'en_proceso', 'resuelta'])],
            'respuesta' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($queja->estado === 'resuelta') {
            return $this->invalido('respuesta', 'La queja ya está resuelta y solo se puede consultar');
        }

        $respuesta = trim($datos['respuesta'] ?? '');

        if ($datos['estado'] === 'resuelta' && $respuesta === '') {
            return $this->invalido('respuesta', 'Escribe una respuesta antes de marcar la queja como resuelta');
        }

        $queja->update(['estado' => $datos['estado'], 'respuesta' => $respuesta !== '' ? $respuesta : $queja->respuesta]);

        return response()->json($queja->fresh()->load(self::RELACIONES));
    }

    private function invalido(string $campo, string $mensaje): JsonResponse
    {
        return response()->json(['mensaje' => 'Los datos no son válidos', 'errors' => [$campo => [$mensaje]]], 422);
    }
}
