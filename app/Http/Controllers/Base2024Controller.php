<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use Carbon\Carbon;


class Base2024Controller extends Controller
{
    public function buscar(Request $request)
    {
        $request->validate([
            'cedula' => 'required'
        ]);

        // Buscar por número de cédula
        $persona = DB::table('base2024')
            ->where('CEDULA', $request->cedula)
            ->first();


            //

                if ($persona) {

    $fechaNacimiento = \Carbon\Carbon::parse($persona->FECHA);
    $edad = $fechaNacimiento->age;

    $categ = Categoria::where('edad_min', '<=', $edad)
        ->where(function ($q) use ($edad) {
            $q->whereNull('edad_max')->orWhere('edad_max', '>=', $edad);
        })
        ->first();

    $categoria=$categ->nombre;    


 
                }

            


            //

        if ($persona) {
            return response()->json([
                'existe' => true,
                'nombres' => $persona->NOMBRES ?? '',
                'apellidos' => $persona->APELLIDOS ?? '',
                'genero' => $persona->GENERO ?? '',
                'fecha_nacimiento' => $persona->{'FECHA'} ?? '',
                'talla' => $persona->TALLA ?? '',
                'telefono' => $persona->TELEFONOCELULAR ?? '',
                'email' => $persona->{'E-MAIL'} ?? '',
                'categoria' => $categoria ?? ''
            ]);
        }

        return response()->json(['existe' => false]);
    }
}
