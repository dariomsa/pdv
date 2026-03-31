<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Participante;
use App\Models\FacturacionCorporativa;
use App\Models\Facturacion;
use App\Models\FacturacionDetalle;
use App\Models\PagoDetalle;
use App\Models\ParticipanteGratuita;
use App\Models\ParticipanteCorporativa;




class ReporteVentasGeneralesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



public function index(Request $request)
{
    $fecha_desde = $request->input('fecha_desde') ?? date('Y-m-d');
    $fecha_hasta = $request->input('fecha_hasta') ?? date('Y-m-d');

    // carrera_id: 1=15K, 2=21K, 3=MINI...
    $carrera = $request->input('carrera');

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES NORMALES (usa inscripcion_tipo.carrera_id)
    |--------------------------------------------------------------------------
    */
    $participantesNormales = Participante::with(['tipoInscripcion', 'creador'])
        ->whereBetween('created_at', ["$fecha_desde 00:00:00", "$fecha_hasta 23:59:59"])
        ->when(!empty($carrera), function ($q) use ($carrera) {
            $q->whereHas('tipoInscripcion', function ($sub) use ($carrera) {
                $sub->where('carrera_id', $carrera);
            });
        })
        ->get()
        ->map(function ($p) {

            $factura  = Facturacion::where('inscripcion_id', $p->inscripcion_id)->first();
            $detalle  = FacturacionDetalle::where('participante_id', $p->id)->first();
            $pago     = PagoDetalle::where('participante_id', $p->id)->with('formaPago')->first();

            return [
                'id' => $p->id,
                'created_at' => $p->created_at,
                'factura_numero' => $factura ? substr($factura->clave_acceso, 24, 15) : 'ND',
                'empresa_factura' => $factura->nombre_facturacion ?? 'ND',
                'telefono_factura' => $p->celular ?? 'ND',
                'nota_facturacion' => $factura->nota_facturacion ?? 'ND',
                'origen' => $p->creador->name ?? 'ND',
                'tipoInscripcion' => $p->tipoInscripcion,
                'tipo_documento' => $p->tipo_documento,
                'numero_documento' => $p->numero_documento,
                'nombres' => $p->nombres,
                'apellidos' => $p->apellidos,
                'genero' => $p->genero,
                'corral' => $p->corral,
                'categoria' => $p->categoria,
                'fecha_nacimiento' => $p->fecha_nacimiento,
                'talla' => $p->talla,
                'email' => $p->email,
                'factura' => $p->factura,
                'metodo_pago' => $pago->formaPago->metodo_pago ?? 'ND',
                'referencia' => $pago->referencia ?? 'ND',
                'sub_total' => $detalle ? number_format($detalle->valor / 1.15, 2) : 'ND',
                'iva' => $detalle ? number_format($detalle->valor - ($detalle->valor / 1.15), 2) : 'ND',
                'total' => $detalle->valor ?? 'ND',
               'discapacidad' => $p->discapacidad == 1 ? 'SI' : 'NO',
			   'subtipo' => $p->sub_tipo,
			   
            ];
        });

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES GRATUITOS (usa inscripcion_tipo.carrera_id)
    |--------------------------------------------------------------------------
    */
    $participantesGratuitos = ParticipanteGratuita::with(['tipoInscripcion', 'creador'])
        ->whereBetween('created_at', ["$fecha_desde 00:00:00", "$fecha_hasta 23:59:59"])
        ->when(!empty($carrera), function ($q) use ($carrera) {
            $q->whereHas('tipoInscripcion', function ($sub) use ($carrera) {
                $sub->where('carrera_id', $carrera);
            });
        })
        ->get()
        ->map(function ($p) {

            return [
                'id' => $p->id,
                'created_at' => $p->created_at,
                'factura_numero' => 'GRATUITO',
                'empresa_factura' => 'GRATUITO',
                'telefono_factura' => $p->celular ?? 'ND',
                'nota_facturacion' => 'ND',
                'origen' => $p->creador->name ?? 'ND',
                'tipoInscripcion' => $p->tipoInscripcion,
                'tipo_documento' => $p->tipo_documento,
                'numero_documento' => $p->numero_documento,
                'nombres' => $p->nombres,
                'apellidos' => $p->apellidos,
                'genero' => $p->genero,
                'corral' => $p->corral,
                'categoria' => $p->categoria,
                'fecha_nacimiento' => $p->fecha_nacimiento,
                'talla' => $p->talla,
                'email' => $p->email,
                'factura' => 'GRATUITO',
                'metodo_pago' => 'GRATUITO',
                'referencia' => 'ND',
                'sub_total' => '0.00',
                'iva' => '0.00',
                'total' => '0.00',
                'discapacidad' => 'ND',
            ];
        });

    /*
    |--------------------------------------------------------------------------
    | PARTICIPANTES CORPORATIVOS (usa inscripcion_tipo_corporativas.id)
    |--------------------------------------------------------------------------
    | Como NO hay carrera_id, mapeamos carrera => ids corporativos
    */
    $mapTiposCorpPorCarrera = [
        1 => [1], // carrera 15K => tipo corporativo id 1
        2 => [5], // carrera 21K => tipo corporativo id 5
        3 => [10], // si algún día hay MINI corporativa, pones el id aquí
    ];

    $tiposCorpIds = !empty($carrera) ? ($mapTiposCorpPorCarrera[(int)$carrera] ?? []) : [];

    $corporativos = ParticipanteCorporativa::with(['tipoInscripcion', 'creador'])
        ->whereBetween('created_at', ["$fecha_desde 00:00:00", "$fecha_hasta 23:59:59"])
        ->when(!empty($carrera), function ($q) use ($tiposCorpIds) {
            // si la carrera seleccionada no tiene mapeo, no debe traer nada
            if (empty($tiposCorpIds)) {
                $q->whereRaw('1=0');
            } else {
                $q->whereIn('tipo_inscripcion', $tiposCorpIds);
            }
        })
        ->get()
        ->map(function ($p) {

            $factura = FacturacionCorporativa::where('inscripcion_id', $p->inscripcion_id)
                ->with('formaPago')
                ->first();

            return [
                'id' => $p->id,
                'created_at' => $p->created_at,
                'factura_numero' => $factura ? substr($factura->clave_acceso, 24, 15) : 'ND',
                'empresa_factura' => $factura->empresa ?? 'ND',
                'telefono_factura' => $p->celular ?? 'ND',
                'nota_facturacion' => $factura->nota_facturacion ?? 'ND',
                'origen' => $p->creador->name ?? 'ND',
                'tipoInscripcion' => $p->tipoInscripcion,
                'tipo_documento' => $p->tipo_documento,
                'numero_documento' => $p->numero_documento,
                'nombres' => $p->nombres,
                'apellidos' => $p->apellidos,
                'genero' => $p->genero,
                'corral' => $p->corral,
                'categoria' => $p->categoria,
                'fecha_nacimiento' => $p->fecha_nacimiento,
                'talla' => $p->talla,
                'email' => $p->email,
                'factura' => 'CORPORATIVAS',
                'metodo_pago' => $factura->formaPago->metodo_pago ?? 'NO APLICA',
                'referencia' => $factura->referencia ?? 'ND',
                'sub_total' => number_format($p->valor / 1.15, 2),
                'iva' => $p->iva ?? '0.00',
                'total' => $p->valor ?? '0.00',
                'discapacidad' => $p->discapacidad == 1 ? 'SI' : 'NO',
				 'subtipo' =>'',
            ];
        });

    $data = collect($participantesNormales)
        ->merge($participantesGratuitos)
        ->merge($corporativos);

    return view('reportes.ventas_generales', compact('data', 'fecha_desde', 'fecha_hasta', 'carrera'));
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
