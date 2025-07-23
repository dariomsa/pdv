<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormaPago;
use App\Models\InscripcionCorporativa;
use App\Models\ParticipanteCorporativa;
use App\Models\ParticipanteCorporativaTemporal;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use App\Models\FacturacionCorporativa;
use App\Imports\ParticipantesCorporativosImport;
use Maatwebsite\Excel\Facades\Excel;

class CorporativasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index()
{
    session()->forget('inscripcion_id');
    $fecha_desde = request('fecha_desde') ?? date('Y-m-d');
    $fecha_hasta = request('fecha_hasta') ?? date('Y-m-d');

    $participantes = ParticipanteCorporativa::with(['tipoInscripcion', 'creador'])
        ->when(request()->filled('fecha_desde'), function ($query) {
            $query->whereDate('created_at', '>=', request('fecha_desde'));
        })
        ->when(request()->filled('fecha_hasta'), function ($query) {
            $query->whereDate('created_at', '<=', request('fecha_hasta'));
        })
        ->get()
        ->map(function ($p) {
            // Obtener factura si existe
            $factura = FacturacionCorporativa::where('inscripcion_id', $p->inscripcion_id)->with('formaPago')->first();

            
            // Simular pago y detalle, en caso de que se use algo similar en el futuro
            $detalle = null; // No hay detalle definido para corporativas
            $pago = null;    // No hay pago para corporativas

            return (object) [
                'id' => $p->id,
                'created_at' => $p->created_at,
                'factura_numero' => $factura ? substr($factura->clave_acceso, 0, 15) : 'ND',
                'empresa_factura' => $factura->empresa ?? 'ND',
                'telefono_factura' => $factura->telefono ?? 'ND',
                'origen' => $p->creador->name ?? 'ND',
                'tipoInscripcion' => $p->tipoInscripcion,
                'tipo_documento' => $p->tipo_documento,
                'numero_documento' => $p->numero_documento,
                'nombres' => $p->nombres,
                'apellidos' => $p->apellidos,
                'genero' => $p->genero,
                'corral' => 'ND',
                'categoria' => $p->categoria,
                'fecha_nacimiento' => $p->fecha_nacimiento,
                'talla' => $p->talla,
                'email' => $p->email,
                'factura' => 'CORPORATIVAS',
                'metodo_pago' => $factura->formaPago->metodo_pago ?? 'NO APLICA',
                'referencia' => $factura->referencia ?? 'ND',
                'sub_total' => $factura ? number_format($factura->valor / 1.15, 2) : '0.00',
                'iva' => $factura ? number_format($factura->valor - ($factura->valor / 1.15), 2) : '0.00',
                'total' => $factura->valor ?? '0.00',
                'discapacidad' => 'ND',
            ];
        });

    return view('corporativas.index', compact('participantes', 'fecha_desde', 'fecha_hasta'));
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
            return redirect()->route('admin.corporativas.resumen')
            ->with('success', 'Revise el resumen antes de finalizar.');

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
        foreach ($participantes as $p) {
            $valor = $p->tipoInscripcion->valor ?? 0;
            $iva=$valor-($valor/(1+$p->tipoInscripcion->iva));
            $valor=$valor-$iva;
            $subtotal += $valor;
            $iva_total += $iva;
        }
        $total = $subtotal+$iva_total;



$formasPago = FormaPago::where('estado', 'A')->get();

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
                'pagado'                 => 1 ,
                'forma_pago_id'  => $request->forma_pago,
            ];

              // dd($data); 
            $factura =FacturacionCorporativa::create ($data);

     
            // Actualizar estado de la inscripción
            InscripcionCorporativa::where('id', $inscripcionId)->update(['estado' => 1]);

            // Detalles por participante
            foreach ($participantes as $p) {
                
             DB::table('inventario_total')
            ->where('talla', $p->talla)
            ->where('stock_restante', '>', 0)
            ->decrement('stock_restante', 1);

         
            }


        DB::commit();
        session()->forget('inscripcion_id');

        return redirect()->route('admin.corporativas.index')->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
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

            
             $participante = ParticipanteCorporativa::create($data);
             
            }
            

            //

           $participantes = ParticipanteCorporativa::with('tipoInscripcion')
                ->where('inscripcion_id', $inscripcionId)
                ->get();
              
      
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
                'forma_pago_id'  => 10,
            ];

              // dd($data); 
            $factura =FacturacionCorporativa::create ($data);

        

            // Detalles por participante
            foreach ($participantes as $p) {
                
             DB::table('inventario_total')
            ->where('talla', $p->talla)
            ->where('stock_restante', '>', 0)
            ->decrement('stock_restante', 1);

         
            }


        DB::commit();
        session()->forget('inscripcion_id');

        return redirect()->route('admin.corporativas.index')->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
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
                'pagado'                 => 1 ,
                'forma_pago_id'  => $request->forma_pago,
            ];

              // dd($data); 
            $factura =FacturacionCorporativa::create ($data);

     
            // Actualizar estado de la inscripción
            InscripcionCorporativa::where('id', $inscripcionId)->update(['estado' => 1]);

            // Detalles por participante
            foreach ($participantes as $p) {
                
             DB::table('inventario_total')
            ->where('talla', $p->talla)
            ->where('stock_restante', '>', 0)
            ->decrement('stock_restante', 1);

         
            }


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
