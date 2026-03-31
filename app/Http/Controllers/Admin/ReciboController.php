<?php

namespace App\Http\Controllers\Admin;

use App\Models\Participante;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;



class ReciboController
{
  
  public function index(Request $request)
{
 $tmps = Participante::select(
        'participantes.*',
        'participantes.id as participante_id',
        'participantes.created_at as creado',
        'facturacion.*',
        'users.name',
        'ti.nombre as carrera_nombre'
    )
    ->join('facturacion', 'participantes.inscripcion_id', '=', 'facturacion.inscripcion_id') // ✅ AQUÍ
    ->join('users', 'participantes.created_by_id', '=', 'users.id')
    ->leftJoin('inscripcion_tipo as ti', 'ti.id', '=', 'participantes.tipo_inscripcion')
    ->where('participantes.id', $request->id)
    ->first();

    if (!$tmps) {
        return response('NO DATA', 404);
    }
	
	//dd($tmps);

    $data_pdf = [
        'FACT1' => substr($tmps->clave_acceso, 25, 15),
        'PUNTO' => $tmps->name,

        // NUEVO:
        'CARRERA' => $tmps->carrera_nombre ?? 'SIN CARRERA',

        'CLIENTE_FACT1' => trim(strtoupper($tmps->nombre_facturacion)),
        'CLIENTE_RUC1' => $tmps->numero_doc_facturacion,
        'EMAIL1' => $tmps->email_facturacion,
        'TELEFONO1' => $tmps->telefono_facturacion,
        'CEDULA' => $tmps->numero_documento,
        'PARTICIPANTE' => trim(strtoupper($tmps->nombres)).' '.trim(strtoupper($tmps->apellidos)),
        'CATEGORIA' => $tmps->categoria,
        'GENERO' => $tmps->genero,
        'TALLA' => $tmps->talla,
        'CORRAL' => $tmps->corral,
        'COMPETIDOR' => $tmps->numero_participante,
        'CANTIDAD1' => 1,
        'SUBTOTAL1' => $tmps->valor - $tmps->iva,
        'IVA1' => $tmps->iva,
        'VALOR1' => $tmps->valor,

        'CLIENTE_FACT2' => trim(strtoupper($tmps->nombre_ruc_facturacion)),
        'CLIENTE_RUC2' => $tmps->numero_doc_facturacion,
        'EMAIL2' => $tmps->email_facturacion,
        'TELEFONO2' => $tmps->telefono_facturacion,
        'CANTIDAD2' => 1,
        'SUBTOTAL2' => $tmps->valor - $tmps->iva,
        'IVA2' => $tmps->iva,
        'VALOR2' => $tmps->valor,
        'FECHA' => substr($tmps->creado, 0, 10),
        'ID' => $tmps->participante_id,
        'img'=> public_path('docs/Comrpobante-OK.jpg.jpeg'),
    ];

    $content = Pdf::loadView('pdfs.recibo', compact('data_pdf'))->output();
    Storage::disk('public')->put('RECIBO-' . $tmps->participante_id . '.pdf', $content);

    return redirect('/storage/RECIBO-' . $tmps->participante_id . '.pdf');
}
  
}
