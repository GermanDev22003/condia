<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Permite el paso solo a usuarios con rol "residente". */
class SoloResidente
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->rol !== 'residente') {
            return response()->json(['mensaje' => 'No tienes permiso para esta acción'], 403);
        }

        return $next($request);
    }
}
