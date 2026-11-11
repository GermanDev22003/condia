<?php

namespace App\Http\Controllers;

use App\Models\Residente;
use App\Models\TokenAcceso;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = Usuario::where('correo', $datos['correo'])->first();

        if (! $usuario || ! $usuario->activo || ! Hash::check($datos['password'], $usuario->password)) {
            return response()->json(['mensaje' => 'Correo o contraseña incorrectos'], 401);
        }

        $plano = Str::random(64);

        TokenAcceso::create([
            'id_usuario' => $usuario->id_usuario,
            'token_hash' => hash('sha256', $plano),
        ]);

        return response()->json([
            'token' => $plano,
            'tipo' => 'Bearer',
            'usuario' => $this->datosUsuario($usuario),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->attributes->get('token_acceso')?->delete();

        return response()->json(['mensaje' => 'Sesión cerrada']);
    }

    /** Cambia la contraseña del usuario autenticado y cierra sus demás sesiones. */
    public function cambiarPassword(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:password_actual'],
        ]);

        $usuario = $request->user();

        if (! Hash::check($datos['password_actual'], $usuario->password)) {
            return response()->json([
                'mensaje' => 'Los datos no son válidos',
                'errors' => ['password_actual' => ['La contraseña actual no es correcta']],
            ], 422);
        }

        $usuario->update(['password' => $datos['password']]);

        // se conserva solo la sesión desde la que se hizo el cambio
        $actual = $request->attributes->get('token_acceso');
        TokenAcceso::where('id_usuario', $usuario->id_usuario)
            ->where('id_token', '!=', $actual?->id_token)
            ->delete();

        return response()->json(['mensaje' => 'Contraseña actualizada']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($this->datosUsuario($request->user()));
    }

    private function datosUsuario(Usuario $usuario): array
    {
        $residente = $usuario->rol === 'residente'
            ? Residente::with('unidad.condominio')->where('id_usuario', $usuario->id_usuario)->first()
            : null;

        return [
            'id_usuario' => $usuario->id_usuario,
            'correo' => $usuario->correo,
            'rol' => $usuario->rol,
            'residente' => $residente,
        ];
    }
}
