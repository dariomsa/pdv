<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Categoria;
use Carbon\Carbon;

class CategoriaController extends Controller
{
    public function obtenerPorFechaNacimiento(Request $request)
{
    $request->validate([
        'fecha_nacimiento' => 'required|date',
    ]);

    $fechaNacimiento = \Carbon\Carbon::parse($request->fecha_nacimiento);
    $edad = $fechaNacimiento->age;

    $categoria = Categoria::where('edad_min', '<=', $edad)
        ->where(function ($q) use ($edad) {
            $q->whereNull('edad_max')->orWhere('edad_max', '>=', $edad);
        })
        ->first();

    return response()->json([
       'categoria' => optional($categoria)->nombre ?? 'Sin categoría',
        'edad' => $edad,
        'tercera_edad' => $edad >= 65 ? 1 : 0
    ]);
}
}
