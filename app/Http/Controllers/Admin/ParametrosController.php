<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParametrosController extends Controller
{
    public function index()
    {
        $categorias = DB::table('categorias')->get();
        $formasPago = DB::table('formas_pago')->get();
        $tiposInscripcion = DB::table('inscripcion_tipo')->get();
        $inventario = DB::table('inventario_total')->get();
        $tiposInscripcionCorp = DB::table('inscripcion_tipo_corporativas')->get();

        return view('parametros.index', compact('categorias', 'formasPago', 'tiposInscripcion', 'tiposInscripcionCorp', 'inventario'));
    }
}
