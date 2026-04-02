<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormaPago;
use App\Models\InscripcionCorporativa;
use App\Models\ParticipanteCorporativa;

use App\Models\Participante;

use App\Models\ParticipanteCorporativaTemporal;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use App\Models\InscripcionTipoCorporativa;
use App\Models\FacturacionCorporativa;
use App\Imports\ParticipantesCorporativosImport;
use Maatwebsite\Excel\Facades\Excel;
use App\helpers\ParticipanteHelper;
use Session;
use Illuminate\Support\Facades\Http;


class CorporativasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


public function index(Request $request)
    {
        session()->forget('inscripcion_id');

        $hoy = date('Y-m-d');

        $fecha_desde = $request->filled('fecha_desde') ? $request->fecha_desde : $hoy;
        $fecha_hasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : $hoy;

        // Nuevo: carrera (15K / 21K) -> en corporativas también filtra por tipo_inscripcion
        $carrera = $request->input('carrera'); // puede venir null/vacío

        $participantes = ParticipanteCorporativa::with(['tipoInscripcion', 'creador'])
           
            // Filtro por carrera solo si viene seleccionada
            ->when(!empty($carrera), function ($query) use ($carrera) {
                $query->where('tipo_inscripcion', $carrera);
            })
            ->whereDate('created_at', '>=', $fecha_desde)
            ->whereDate('created_at', '<=', $fecha_hasta)
            ->get()
            ->map(function ($p) {

                // Factura corporativa (si existe)
                $factura = FacturacionCorporativa::where('inscripcion_id', $p->inscripcion_id)
                    ->with('formaPago')
                    ->first();

                return (object)[
                    'id' => $p->id,
                    'created_at' => $p->created_at,

                    'factura_numero' => $factura ? substr($factura->clave_acceso, 24, 15) : 'ND',
                    'empresa_factura' => $factura->empresa ?? 'ND',
                    'telefono_factura' => $factura->telefono ?? 'ND',

                    'origen' => $p->creador->name ?? 'ND',
                    'tipoInscripcion' => $p->tipoInscripcion,

                    'tipo_documento' => $p->tipo_documento,
                    'numero_documento' => $p->numero_documento,
                    'nombres' => $p->nombres,
                    'apellidos' => $p->apellidos,
                    'genero' => $p->genero,
					
					  'inscripcion_id' => $p->inscripcion_id,

                    'corral' => $p->corral,
                    'categoria' => $p->categoria,
                    'fecha_nacimiento' => $p->fecha_nacimiento,
                    'talla' => $p->talla,
                    'email' => $p->email,
					
					'certificado' => $p->certificado,

                    'factura' => 'CORPORATIVAS',

                    'metodo_pago' => $factura->formaPago->metodo_pago ?? 'NO APLICA',
                    'referencia' => $factura->referencia ?? 'ND',

                    'sub_total' => $p->valor !== null ? number_format($p->valor / 1.15, 2) : '0.00',
                    'iva' => $p->iva ?? '0.00',
                    'total' => $p->valor ?? '0.00',

                    'discapacidad' => ($p->discapacidad == 1) ? 'SI' : 'NO',
                ];
            });
			
			//dd( $participantes);

        return view('corporativas.index', compact('participantes', 'fecha_desde', 'fecha_hasta', 'carrera'));
    }
	
	

public function marcarCertificado(Request $request, $id)
{
	
	
    try {
        DB::beginTransaction();

        // 1️⃣ Obtener todos los participantes de la inscripción
        $participantes = ParticipanteCorporativa::where('inscripcion_id', $id)->get();

        if ($participantes->isEmpty()) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No existen participantes para esta inscripción.'
            ], 404);
        }

        // 2️⃣ Armar JSON para el API (estructura del primer API)
 $payload = [
    'inscripcion_id' => (int) $id,
    'created_by_id' => (int) $participantes->first()->created_by_id,
    'participantes' => $participantes->map(function ($p) {
        return [
            'tipo_inscripcion' => (int) $p->tipo_inscripcion,
            'tipo_documento' => $p->tipo_documento,
            'numero_documento' => $p->numero_documento,
            'nombres' => $p->nombres,
            'apellidos' => $p->apellidos,
            'nacionalidad' => $p->nacionalidad,
            'genero' => $p->genero,
            'fecha_nacimiento' => $p->fecha_nacimiento,
            'categoria' => $p->categoria,
            'talla' => $p->talla,
            'celular' => $p->celular,
            'email' => $p->email,
            'direccion' => $p->direccion,
            'provincia' => $p->provincia,
            'ciudad' => $p->ciudad,
            'parroquia' => $p->parroquia,
            'corral' => $p->corral,
            'tercera_edad' => (int) ($p->tercera_edad ?? 0),
            'discapacidad' => (int) ($p->discapacidad ?? 0),
            'valor' => (float) $p->valor,
            'iva' => (float) $p->iva,
            'certificado' => 0,
        ];
    })->toArray()
];

		
		

        // 3️⃣ Consumir el API de certificados
        $response = Http::timeout(20)
            ->acceptJson()
            ->post(config('services.certificados.url'), $payload);
			
			
			//dd($response );

        if (!$response->successful()) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $response->body(),
                'api_response' => $response->body()
            ], 502);
        }

        $apiResponse = $response->json();

        if (!isset($apiResponse['success']) || $apiResponse['success'] !== true) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'El API no confirmó la emisión de certificados.',
                'api_response' => $apiResponse
            ], 422);
        }

        // 4️⃣ API OK → marcar certificados en BD
        $updated = ParticipanteCorporativa::where('inscripcion_id', $id)
            ->update(['certificado' => 1]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Se envío correctamente a la cola de correo.',
            'inscripcion_id' => (int) $id,
            'participantes' => $participantes->count(),
            'updated' => $updated
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error interno al emitir certificados.',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function pdv()
{
    try {

        DB::beginTransaction();

        // 1️⃣ Traer todos los pendientes
        $participantes = Participante::where('created_by_id', '!=', 8)
            ->where(function ($q) {
                $q->whereNull('certificado')
                  ->orWhere('certificado', 0);
            })
            ->get();

        if ($participantes->isEmpty()) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'No existen participantes pendientes.'
            ], 404);
        }

        // 2️⃣ Agrupar por inscripcion_id
        $grupos = $participantes->groupBy('inscripcion_id');

        foreach ($grupos as $inscripcionId => $grupoParticipantes) {

            $createdById = $grupoParticipantes->first()->created_by_id;

            $payload = [
                'inscripcion_id' => (int) $inscripcionId,
                'created_by_id'  => (int) $createdById,
                'participantes'  => $grupoParticipantes->map(function ($p) {
                    return [
                        'tipo_inscripcion' => (int) $p->tipo_inscripcion,
                        'tipo_documento'   => $p->tipo_documento,
                        'numero_documento' => $p->numero_documento,
                        'nombres'          => $p->nombres,
                        'apellidos'        => $p->apellidos,
                        'nacionalidad'     => $p->nacionalidad,
                        'genero'           => $p->genero,
                        'fecha_nacimiento' => $p->fecha_nacimiento,
                        'categoria'        => $p->categoria,
                        'talla'            => $p->talla,
                        'celular'          => $p->celular,
                        'email'            => $p->email,
                        'direccion'        => $p->direccion,
                        'provincia'        => $p->provincia,
                        'ciudad'           => $p->ciudad,
                        'parroquia'        => $p->parroquia,
                        'corral'           => $p->corral,
                        'tercera_edad'     => (int) ($p->tercera_edad ?? 0),
                        'discapacidad'     => (int) ($p->discapacidad ?? 0),
                        'valor'            => (float) $p->valor,
                        'iva'              => (float) $p->iva,
                        'certificado'      => 0,
                    ];
                })->values()->toArray()
            ];
			
			

            // 🔥 Consumir API por cada inscripción
            $response = Http::timeout(20)
                ->acceptJson()
                ->post(config('services.pdv.url'), $payload);

            if (!$response->successful()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error API',
                    'api_response' => $response->body()
                ], 502);
            }

            $apiResponse = $response->json();

            if (!isset($apiResponse['success']) || $apiResponse['success'] !== true) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'API no confirmó emisión',
                    'api_response' => $apiResponse
                ], 422);
            }

            // 3️⃣ Marcar solo los de esa inscripción como enviados
            Participante::where('inscripcion_id', $inscripcionId)
                ->whereIn('id', $grupoParticipantes->pluck('id'))
                ->update(['certificado' => 1]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Certificados enviados correctamente.',
            'total_inscripciones' => $grupos->count(),
            'total_participantes' => $participantes->count()
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => 'Error interno',
            'error' => $e->getMessage()
        ], 500);
    }
}




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('corporativas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function store(Request $request)
    {
        $request->validate([
            'archivo_excel' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            DB::beginTransaction();

                // Obtener o crear inscripción
            $inscripcionId = session('inscripcion_id');
     
            if (!$inscripcionId) {

                $inscripcion = InscripcionCorporativa::create([
                    'created_by_id' => auth()->id(),
                ]);

       			session(['inscripcion_id' => $inscripcion->id]);
				$inscripcionId=$inscripcion->id;
        			
            } else { 

                  $inscripcion = InscripcionCorporativa::findOrFail($inscripcionId);
                 
            }


        $imported=Excel::import(new \App\Imports\ParticipantesCorporativosImport($inscripcionId), $request->file('archivo_excel'));
  
        $data = $request->all();

    
 
            session(['inscripcion_corporativa_id' =>$inscripcionId]);

            DB::commit();
            return redirect()->route('admin.corporativas.resumen');


        } catch (\Exception $e) {
            DB::rollBack();

            dd($e->getMessage());
            return redirect()->back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }


 
public function resumen(Request $request)
{
    $inscripcionId = session('inscripcion_id');

    if (!$inscripcionId) {
        return redirect()->route('admin.corporativas.create')
            ->with('error', 'No hay inscripción activa.');
    }


    $inscripcion = InscripcionCorporativa::with('participantesTemporales')->findOrFail($inscripcionId);
     
    
    
    if (!$inscripcion || $inscripcion->participantesTemporales->isEmpty()) {
        return redirect()->route('inscripciones.create')
            ->with('error', 'No hay participantes registrados en esta inscripción.');
    }

     


$participantes = ParticipanteCorporativaTemporal::with('tipoInscripcion')
    ->where('inscripcion_id', $inscripcionId)
    ->get();
	    $primer = $participantes->take(10);
	

$subtotal = 0;
$iva_total = 0;
$descuento_total = 0;



foreach ($participantes as $p) {

    $valorBase = $p->tipoInscripcion->valor ?? 0;
    $ivaRate   = $p->tipoInscripcion->iva ?? 0; // ej 0.12

    $descuento = 0;

    // 1) Descuento por tercera edad (50%)
    if (!empty($p->fecha_nacimiento)) {
        $edad = \Carbon\Carbon::parse($p->fecha_nacimiento)->age;

        if ($edad >= 65) {
            $descuento = max($descuento, $valorBase * 0.50);
        }
    }

    // 2) Descuento por discapacidad (50%)
    $flagDiscap = strtoupper(trim((string)($p->discapacidad ?? '')));
    $tieneDiscapacidad = in_array($flagDiscap, ['SI', 'SÍ'], true);

    if ($tieneDiscapacidad) {
        $descuento = max($descuento, $valorBase * 0.50);
    }

    $valorConDescuento = $valorBase - $descuento;

    // 3) Separar IVA (asumiendo que $valorConDescuento incluye IVA)
    $iva = $valorConDescuento - ($valorConDescuento / (1 + $ivaRate));
    $valorSinIva = $valorConDescuento - $iva;

    $subtotal        += $valorSinIva;
    $iva_total       += $iva;
    $descuento_total += $descuento;
}



$total = $subtotal + $iva_total;


$user = auth()->user();

$formasPago = FormaPago::when($user->id != 10, function ($q) {
        $q->where('estado', 'A');
    })
    ->get();

return view('corporativas.resumen', compact(
    'inscripcion',
    'participantes',
    'subtotal',
    'iva_total',
    'total',
    'formasPago','primer'
));


}



public function finalizar(Request $request)
{

            $request->validate([
           
            'numero_documento' => 'required',
            'razon_social' => 'required',
            'empresa' => 'required',
            'email' => 'required',
            'telefono' => 'required',
            'direccion' => 'required',
        
                 

          
        ]);

 

////
    $inscripcionId = session('inscripcion_id');

              
    if (!$inscripcionId) {
        return redirect()->route('admin.corporativas.create')->with('error', 'No hay inscripción activa.');
    }

  
        try {
            // Verificar si ya existe facturación para la inscripción
                 

            DB::beginTransaction();
            
            $temporales = ParticipanteCorporativaTemporal::where('inscripcion_id', $inscripcionId)->get();
                 
            
   foreach ($temporales as $temp) {
    $data = $temp->toArray();
    unset($data['id']); // Eliminar ID para evitar conflictos de duplicado

  
	


        // Asumo que InscripcionCorporativa tiene el campo tipo_inscripcion_id
        $tipoInscripcion = InscripcionTipoCorporativa::findOrFail($data['tipo_inscripcion']);

        // 1 = CORPORATIVAS 15K, 5 = CORPORATIVAS 21K
        // Mapeo a carrera_id: 1 => 15K, 2 => 21K
        if ($tipoInscripcion->id == 1) {
            $carreraId = 1;   // 15K
        } elseif ($tipoInscripcion->id == 5) {
            $carreraId = 2;   // 21K
        }
         elseif ($tipoInscripcion->id == 10) {
            $carreraId = 3;   // 21K
        } 		else {
            $carreraId = 1;   // por defecto 15K (ajusta si quieres)
        }


    // Edad y tercera edad
    $fechaNacimiento = \Carbon\Carbon::parse($data['fecha_nacimiento']);
    $edad = $fechaNacimiento->age;
    $data['tercera_edad'] = $edad >= 65 ? 1 : 0;

    // Discapacidad: convertir a 0 o 1
    $data['discapacidad'] = (
        isset($data['discapacidad']) && strtoupper(trim($data['discapacidad'])) === 'SI'
    ) ? 1 : 0;

// 3) Categoría por edad + carrera
               
         $categoria = null;

        if ($carreraId === 3) {
            $anio = (int) $fechaNacimiento->format('Y');

            $categoria = Categoria::where('carrera_id', $carreraId)
                ->where('edad_min', '<=', $anio)
                ->where('edad_max', '>=', $anio)
                ->orderBy('edad_min', 'asc')
                ->first();
        } else {
            $categoria = Categoria::where('carrera_id', $carreraId)
                ->where('edad_min', '<=', $edad)
                ->where(function ($q) use ($edad) {
                    $q->whereNull('edad_max')
                      ->orWhere('edad_max', '>=', $edad);
                })
                ->orderBy('edad_min', 'asc')
                ->first();
        }

   


            $data['categoria'] = $categoria->nombre ?? 'SIN CATEGORÍA';


    // Descuento si tercera edad o discapacidad
    //$tipoInscripcion = InscripcionTipoCorporativa::where('id', 1)->first();
    $valorBase = $tipoInscripcion->valor ?? 0;
    $aplicaDescuento = $data['tercera_edad'] == 1 || $data['discapacidad'] == 1;
    $valorFinal = $aplicaDescuento ? $valorBase / 2 : $valorBase;

    // Guardar si tienes campos en tabla
    $data['valor'] = $valorFinal;

    // Puedes usar $valorFinal para cálculos o guardarlo si es necesario
    $data['valor']= $valorFinal;
   
    $ValorIVA = $valorFinal/ (1 + $tipoInscripcion->iva);
    $iva=$valorFinal-$ValorIVA;
    $data['iva']= $iva;

    //fecha

    $data['created_at'] = $temp->created_at;
    $data['updated_at'] = $temp->updated_at;

 
    // Crear participante
    $participante = ParticipanteCorporativa::create($data);
}         
   
            

           $participantes = ParticipanteCorporativa::with('tipoInscripcion')
                ->where('inscripcion_id', $inscripcionId)
                ->get();
              
                

            $subtotal = $participantes->sum(function ($p) {
                return $p->valor ?? 0;
            });

           

            $iva = $participantes->sum(function ($p) {
                return $p->iva ?? 0;
            });
       
            $data= [
                'inscripcion_id'         => $inscripcionId ,
                'tipo_documento'    => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'razon_social'     => $request->razon_social,
                'empresa'   => $request->empresa,
                'email'      => $request->email,
                'telefono'   => $request->telefono,
                'direccion'  => $request->direccion,
                'nota_adicional'       => $request->nota_adicional,
                'valor'                  => $subtotal,
                'iva'                    => $iva,
                'pagado'                 => 1 ,
                'forma_pago_id'  => $request->forma_pago,
                'referencia'  => $request->referencia,
            ];

              // dd($data); 
            $factura =FacturacionCorporativa::create ($data);
            $id_facturacion=$factura->id;

     
            // Actualizar estado de la inscripción
            InscripcionCorporativa::where('id', $inscripcionId)->update(['estado' => 1]);

            // Detalles por participante
       foreach ($participantes as $p) {

    

    DB::table('inventario_total')
        ->where('talla', $p->talla)
        ->where('genero', $p->genero)
        ->where('carrera_id', $carreraId)
        ->decrement('stock_restante', 1);
}

        DB::commit();
        Session::put('establecimiento', $id_facturacion);
        session()->forget('inscripcion_id');

        return redirect()->route('admin.corporativas.index')->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
       // session()->forget('inscripcion_id');
        return redirect()->back()->with('error', 'Error al finalizar: ' . $e->getMessage());
    }
}



public function gratuitas(Request $request)
{

          
            $request->validate([
           
            'numero_documento' => 'required',
            'razon_social' => 'required',
            'empresa' => 'required',
            'email' => 'required',
            'telefono' => 'required',
            'direccion' => 'required',
        
                 

          
        ]);

 

////
    $inscripcionId = session('inscripcion_id');

              
    if (!$inscripcionId) {
        return redirect()->route('admin.corporativas.create')->with('error', 'No hay inscripción activa.');
    }

  
        try {
            // Verificar si ya existe facturación para la inscripción
                 

            DB::beginTransaction();
            
            $temporales = ParticipanteCorporativaTemporal::where('inscripcion_id', $inscripcionId)->get();
                 
            
   foreach ($temporales as $temp) {
    $data = $temp->toArray();
    unset($data['id']); // Eliminar ID para evitar conflictos de duplicado

  
	


        // Asumo que InscripcionCorporativa tiene el campo tipo_inscripcion_id
        $tipoInscripcion = InscripcionTipoCorporativa::findOrFail($data['tipo_inscripcion']);

        // 1 = CORPORATIVAS 15K, 5 = CORPORATIVAS 21K
        // Mapeo a carrera_id: 1 => 15K, 2 => 21K
        if ($tipoInscripcion->id == 1) {
            $carreraId = 1;   // 15K
        } elseif ($tipoInscripcion->id == 5) {
            $carreraId = 2;   // 21K
        }
         elseif ($tipoInscripcion->id == 10) {
            $carreraId = 3;   // 21K
        } 		else {
            $carreraId = 1;   // por defecto 15K (ajusta si quieres)
        }


    // Edad y tercera edad
    $fechaNacimiento = \Carbon\Carbon::parse($data['fecha_nacimiento']);
    $edad = $fechaNacimiento->age;
    $data['tercera_edad'] = $edad >= 65 ? 1 : 0;

    // Discapacidad: convertir a 0 o 1
    $data['discapacidad'] = (
        isset($data['discapacidad']) && strtoupper(trim($data['discapacidad'])) === 'SI'
    ) ? 1 : 0;

// 3) Categoría por edad + carrera
               
         $categoria = null;

        if ($carreraId === 3) {
            $anio = (int) $fechaNacimiento->format('Y');

            $categoria = Categoria::where('carrera_id', $carreraId)
                ->where('edad_min', '<=', $anio)
                ->where('edad_max', '>=', $anio)
                ->orderBy('edad_min', 'asc')
                ->first();
        } else {
            $categoria = Categoria::where('carrera_id', $carreraId)
                ->where('edad_min', '<=', $edad)
                ->where(function ($q) use ($edad) {
                    $q->whereNull('edad_max')
                      ->orWhere('edad_max', '>=', $edad);
                })
                ->orderBy('edad_min', 'asc')
                ->first();
        }

   


            $data['categoria'] = $categoria->nombre ?? 'SIN CATEGORÍA';


    // Descuento si tercera edad o discapacidad
    //$tipoInscripcion = InscripcionTipoCorporativa::where('id', 1)->first();
    $valorBase = $tipoInscripcion->valor ?? 0;
    $aplicaDescuento = $data['tercera_edad'] == 1 || $data['discapacidad'] == 1;
    $valorFinal = $aplicaDescuento ? $valorBase / 2 : $valorBase;

    // Guardar si tienes campos en tabla
    $data['valor'] = 0;

    // Puedes usar $valorFinal para cálculos o guardarlo si es necesario
 
    $data['iva']= 0;

    //fecha

    $data['created_at'] = $temp->created_at;
    $data['updated_at'] = $temp->updated_at;

 
    // Crear participante
    $participante = ParticipanteCorporativa::create($data);
}         
   
            

           $participantes = ParticipanteCorporativa::with('tipoInscripcion')
                ->where('inscripcion_id', $inscripcionId)
                ->get();
              
                

            $subtotal = $participantes->sum(function ($p) {
                return $p->valor ?? 0;
            });

           

            $iva = $participantes->sum(function ($p) {
                return $p->iva ?? 0;
            });
       
           $data= [
                'inscripcion_id'         => $inscripcionId ,
                'tipo_documento'    => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'razon_social'     => $request->razon_social,
                'empresa'   => $request->empresa,
                'email'      => $request->email,
                'telefono'   => $request->telefono,
                'direccion'  => $request->direccion,
                'nota_adicional'       => $request->nota_adicional,
                'valor'                  => 0,
                'iva'                    => 0,
                'pagado'                 => 0 ,
                'forma_pago_id'  => 1,
                'clave_acceso'  =>'00000000GR-'.$inscripcionId.'00000000000',
            ];

              // dd($data); 
            $factura =FacturacionCorporativa::create ($data);
            $id_facturacion=$factura->id;

     
            // Actualizar estado de la inscripción
            InscripcionCorporativa::where('id', $inscripcionId)->update(['estado' => 1]);

            // Detalles por participante
       foreach ($participantes as $p) {

    

    DB::table('inventario_total')
        ->where('talla', $p->talla)
        ->where('genero', $p->genero)
        ->where('carrera_id', $carreraId)
        ->decrement('stock_restante', 1);
}

        DB::commit();
        Session::put('establecimiento', $id_facturacion);
        session()->forget('inscripcion_id');

        return redirect()->route('admin.corporativas.index')->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
       // session()->forget('inscripcion_id');
        return redirect()->back()->with('error', 'Error al finalizar: ' . $e->getMessage());
    }


}


public function linkpago(Request $request)
{

            $request->validate([
           
            'numero_documento' => 'required',
            'razon_social' => 'required',
            'empresa' => 'required',
            'email' => 'required',
            'telefono' => 'required',
            'direccion' => 'required',
        
                 

          
        ]);

 

////
    $inscripcionId = session('inscripcion_id');

              
    if (!$inscripcionId) {
        return redirect()->route('admin.corporativas.create')->with('error', 'No hay inscripción activa.');
    }

  
        try {
            // Verificar si ya existe facturación para la inscripción
          
          

            DB::beginTransaction();
            
            $temporales = ParticipanteCorporativaTemporal::where('inscripcion_id', $inscripcionId)->get();
                 
            
            
            foreach ($temporales as $temp) {
             $data = $temp->toArray();
             unset($data['id']); // Eliminar ID para evitar conflictos de duplicado
            
             $fechaNacimiento = \Carbon\Carbon::parse($data['fecha_nacimiento']);
            $edad = $fechaNacimiento->age;

            $categoria = Categoria::where('edad_min', '<=', $edad)
            ->where(function ($q) use ($edad) {
            $q->whereNull('edad_max')->orWhere('edad_max', '>=', $edad);
             })
            ->first();
             $data['categoria']=$categoria->nombre;

            
             $participante = ParticipanteCorporativa::create($data);
             
            }
            

            //

           $participantes = ParticipanteCorporativa::with('tipoInscripcion')
                ->where('inscripcion_id', $inscripcionId)
                ->get();
              
                

            $subtotal = $participantes->sum(function ($p) {
                return $p->tipoInscripcion->valor ?? 0;
            });

           

            $iva = $participantes->sum(function ($p) {
                return $p->tipoInscripcion->iva ?? 0;
            });
       
            $data= [
                'inscripcion_id'         => $inscripcionId ,
                'tipo_documento'    => $request->tipo_documento,
                'numero_documento' => $request->numero_documento,
                'razon_social'     => $request->razon_social,
                'empresa'   => $request->empresa,
                'email'      => $request->email,
                'telefono'   => $request->telefono,
                'direccion'  => $request->direccion,
                'nota_adicional'       => $request->nota_adicional,
                'valor'                  => $subtotal,
                'iva'                    => $iva,
                'forma_pago_id'  => $request->forma_pago,
            ];

              // dd($data); 
            $factura =FacturacionCorporativa::create ($data);



        DB::commit();
        session()->forget('inscripcion_id');

        return redirect()->route('admin.corporativas.index')->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error al finalizar: ' . $e->getMessage());
    }
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
