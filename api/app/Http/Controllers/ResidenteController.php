<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\TokenAcceso;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ResidenteController extends Controller
{
    private const RELACIONES = ['unidad', 'usuario:id_usuario,correo,activo'];

    public function index(Request $request): JsonResponse
    {
        $consulta = Residente::with(self::RELACIONES)
            ->orderBy('apellidos')
            ->orderBy('nombres');

        if ($request->filled('estado')) {
            $consulta->where('estado', $request->query('estado'));
        }

        if ($request->filled('buscar')) {
            $texto = '%'.$request->query('buscar').'%';
            $consulta->where(function ($q) use ($texto) {
                $q->where('nombres', 'like', $texto)
                    ->orWhere('apellidos', 'like', $texto)
                    ->orWhereHas('unidad', fn ($u) => $u->where('numero', 'like', $texto));
            });
        }

        return response()->json($consulta->paginate(15));
    }

    public function show(Residente $residente): JsonResponse
    {
        return response()->json($residente->load(self::RELACIONES));
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'id_unidad' => ['required', 'integer', 'exists:unidades,id_unidad'],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_ingreso' => ['required', 'date'],
            'estado' => ['sometimes', Rule::in(['activo', 'inactivo'])],
            // Opcionales: si se envían, se crea también el usuario de acceso.
            'correo' => ['nullable', 'email', 'max:150', 'unique:usuarios,correo'],
            'password' => ['required_with:correo', 'nullable', 'string', 'min:8'],
        ]);

        $residente = DB::transaction(function () use ($datos) {
            $idUsuario = null;

            if (! empty($datos['correo'])) {
                $idUsuario = Usuario::create([
                    'correo' => $datos['correo'],
                    'password' => $datos['password'],
                    'rol' => 'residente',
                    'activo' => true,
                ])->id_usuario;
            }

            return Residente::create([
                'id_usuario' => $idUsuario,
                'id_unidad' => $datos['id_unidad'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'telefono' => $datos['telefono'] ?? null,
                'fecha_ingreso' => $datos['fecha_ingreso'],
                'estado' => $datos['estado'] ?? 'activo',
            ]);
        });

        return response()->json($residente->load(self::RELACIONES), 201);
    }

    public function update(Request $request, Residente $residente): JsonResponse
    {
        $datos = $request->validate([
            'id_unidad' => ['sometimes', 'integer', 'exists:unidades,id_unidad'],
            'nombres' => ['sometimes', 'string', 'max:100'],
            'apellidos' => ['sometimes', 'string', 'max:100'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'fecha_ingreso' => ['sometimes', 'date'],
            'estado' => ['sometimes', Rule::in(['activo', 'inactivo'])],
            'correo' => [
                'sometimes', 'email', 'max:150',
                Rule::unique('usuarios', 'correo')->ignore($residente->id_usuario, 'id_usuario'),
            ],
        ]);

        if (isset($datos['correo']) && ! $residente->usuario) {
            return response()->json(['mensaje' => 'Este residente no tiene usuario de acceso'], 422);
        }

        DB::transaction(function () use ($residente, $datos) {
            $residente->update(Arr::only($datos, [
                'id_unidad', 'nombres', 'apellidos', 'telefono', 'fecha_ingreso', 'estado',
            ]));

            if ($residente->usuario) {
                $cambios = [];
                if (isset($datos['correo'])) {
                    $cambios['correo'] = $datos['correo'];
                }
                if (isset($datos['estado'])) {
                    $cambios['activo'] = $datos['estado'] === 'activo';
                }
                if ($cambios) {
                    $residente->usuario->update($cambios);
                }
            }
        });

        return response()->json($residente->fresh()->load(self::RELACIONES));
    }

    /** Baja lógica: el residente queda "inactivo" y su usuario deja de poder entrar. */
    public function destroy(Residente $residente): JsonResponse
    {
        DB::transaction(function () use ($residente) {
            $residente->update(['estado' => 'inactivo']);

            if ($residente->id_usuario) {
                $residente->usuario->update(['activo' => false]);
                TokenAcceso::where('id_usuario', $residente->id_usuario)->delete();
            }
        });

        return response()->json(['mensaje' => 'Residente dado de baja']);
    }
}
