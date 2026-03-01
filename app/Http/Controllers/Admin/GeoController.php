<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeoController extends Controller
{
    // Ajusta nombres de columnas si en tu tabla `pais` se llaman distinto
    // Asumo: pais_codigo, provincia, canton, parroquia

    public function provincias(Request $request)
    {
     $provincias = DB::table('pais')
    ->select('Provincia')
    ->distinct()
    ->orderBy('Provincia')
    ->pluck('Provincia');


        return response()->json($provincias);
    }

    public function cantones(Request $request)
    {
        $provincia = trim((string)$request->get('provincia'));

        if ($provincia === '') return response()->json([]);

        $cantones = DB::table('pais')
            ->where('Provincia', $provincia)
            ->select('Cantón')
            ->distinct()
            ->orderBy('Cantón')
            ->pluck('Cantón');

        return response()->json($cantones);
    }

    public function parroquias(Request $request)
    {
            $provincia = trim((string)$request->get('provincia'));
        $canton    = trim((string)$request->get('canton'));

        if ($provincia === '' || $canton === '') return response()->json([]);

        $parroquias = DB::table('pais')
            ->where('Provincia', $provincia)
            ->where('Cantón', $canton)
            ->select('Parroquia')
            ->distinct()
            ->orderBy('Parroquia')
            ->pluck('Parroquia');

        return response()->json($parroquias);
    }
}
