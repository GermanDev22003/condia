<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Permite el paso solo a usuarios con rol "administrador". */
class SoloAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->rol !== 'administrador') {
            return response()->json(['mensaje' => 'No tienes permiso para esta acción'], 403);
        }

        return $next($request);
    }
}
