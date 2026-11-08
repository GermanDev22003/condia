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
