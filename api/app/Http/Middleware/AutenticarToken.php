<?php

namespace App\Http\Middleware;

use App\Models\TokenAcceso;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige el encabezado "Authorization: Bearer <token>".
 * Busca el token (guardado como hash) y deja al usuario disponible en $request->user().
 */
class AutenticarToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plano = $request->bearerToken();

        if (! $plano) {
            return $this->noAutenticado();
        }

        $token = TokenAcceso::with('usuario')
            ->where('token_hash', hash('sha256', $plano))
            ->first();

        if (! $token || ! $token->usuario || ! $token->usuario->activo) {
            return $this->noAutenticado();
        }

        $token->forceFill(['ultimo_uso' => now()])->save();

        $usuario = $token->usuario;
        $request->setUserResolver(fn () => $usuario);
        $request->attributes->set('token_acceso', $token);

        return $next($request);
    }

    private function noAutenticado(): Response
    {
        return response()->json(['mensaje' => 'No autenticado'], 401);
    }
}
