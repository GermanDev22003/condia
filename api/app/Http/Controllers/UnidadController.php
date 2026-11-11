<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use Illuminate\Http\JsonResponse;

class UnidadController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Unidad::with('condominio:id_condominio,nombre')->orderBy('numero')->get()
        );
    }
}
