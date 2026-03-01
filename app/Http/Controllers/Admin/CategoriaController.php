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
        'carrera_id'       => 'required|integer',
    ]);

    $carreraId = (int) $request->carrera_id;

    $fechaNacimiento = Carbon::parse($request->fecha_nacimiento);
    $edad = $fechaNacimiento->age;

    $categoria = null;

    // ✅ Caso especial: carrera 3 (5K) está por AÑO en tu tabla (2009-2011, 2012-2013, etc.)
    if ($carreraId === 3) {
        $anio = (int) $fechaNacimiento->format('Y');

        $categoria = Categoria::where('carrera_id', $carreraId)
            ->where('edad_min', '<=', $anio)  // aquí edad_min/edad_max representan año
            ->where('edad_max', '>=', $anio)
            ->orderBy('edad_min', 'asc')
            ->first();
    } else {
        // ✅ Carreras 1 y 2: por EDAD (edad_min / edad_max)
        $categoria = Categoria::where('carrera_id', $carreraId)
            ->where('edad_min', '<=', $edad)
            ->where(function ($q) use ($edad) {
                $q->whereNull('edad_max')
                  ->orWhere('edad_max', '>=', $edad);
            })
            ->orderBy('edad_min', 'asc')
            ->first();
    }

    return response()->json([
        'categoria'    => $categoria ? $categoria->nombre : 'Sin categoría',
        'edad'         => $edad,
        'tercera_edad' => $edad >= 65 ? 1 : 0,
        'carrera_id'   => $carreraId,
    ]);
}
}
