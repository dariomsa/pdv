<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inscripcion;
use App\Models\Participante;
use App\Models\ParticipanteTemporal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria;
use App\Models\InscripcionTipo;
use App\Models\Facturacion;
use App\Models\FacturacionDetalle;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\Pais;
use App\Models\FormaPago;
use Illuminate\Support\Facades\Auth;
use App\helpers\ParticipanteHelper;


class InscripcionesController extends Controller
{
    

    public function index(Request $request)
{
    session()->forget('inscripcion_id');

    $hoy = date('Y-m-d');
    $fecha_desde = $request->filled('fecha_desde') ? $request->fecha_desde : $hoy;
    $fecha_hasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : $hoy;

    $participantes = Participante::with(['tipoInscripcion'])
        ->when(!tieneRol('Administrador_oficina'), function ($query) {
            $query->where('created_by_id', Auth::id());
        })
        ->whereDate('created_at', '>=', $fecha_desde)
        ->whereDate('created_at', '<=', $fecha_hasta)
        ->get()
        ->map(function ($p) {
            $factura = Facturacion::where('inscripcion_id', $p->inscripcion_id)->first();
            $detalle = FacturacionDetalle::where('participante_id', $p->id)->first();
            $pago = PagoDetalle::where('participante_id', $p->id)->with('formaPago')->first();

            return (object)[
                'id' => $p->id,
                'created_at' => $p->created_at,
                'factura_numero' => $factura ? substr($factura->clave_acceso, 0, 15) : 'ND',
                'empresa_factura' => $factura->nombre_facturacion ?? 'ND',
                'telefono_factura' => $factura->telefono_facturacion ?? 'ND',
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
                'factura' => $p->factura,
                'metodo_pago' => $pago->formaPago->metodo_pago ?? 'ND',
                'referencia' => $pago->referencia ?? 'ND',
                'sub_total' => $detalle ? number_format($detalle->valor / 1.15, 2) : 'ND',
                'iva' => $detalle ? number_format($detalle->valor - ($detalle->valor / 1.15), 2) : 'ND',
                'total' => $detalle->valor ?? 'ND',
                'discapacidad' => 'ND',
            ];
        });

    return view('inscripciones.index', compact('participantes', 'fecha_desde', 'fecha_hasta'));
}

    public function create(Request $request)
    {
        $tallasDisponibles = DB::table('inventario_total')
        ->where('stock_restante', '>', 0)
        ->orderBy('id')
        ->get();
        $paises = Pais::orderBy('id')->get();
        $inscripcionId = $request->session()->get('inscripcion_id');
        return view('inscripciones.create', compact('inscripcionId','paises','tallasDisponibles'));
    }

    public function store(Request $request)
    {

        if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Debe iniciar sesión para realizar la inscripción.');
        }

        $request->validate([
            'tipo_inscripcion' => 'required|integer',
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string|max:10',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'genero' => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'required|string',
            'celular' => 'required|string|max:10',
            'talla' => 'required',
        ]);

 if (ParticipanteHelper::yaInscrito($request->numero_documento)) {
        return redirect()->back()->with('success', 'El participante '.$request->numero_documento. '  ya está inscrito en otra modalidad.');
    }

         // Calcular edad
            $fechaNacimiento = \Carbon\Carbon::parse($request->fecha_nacimiento);
            $edad = $fechaNacimiento->age;
       
            if ($edad < 15) {
             return back()->with('error', 'La fecha de nacimiento no es válida. El participante debe tener al menos 1 año.');
            }
           

        // verifica stock de camisetas
        $stock = DB::table('inventario_total')
        ->where('talla', $request->talla)
        ->value('stock_restante');

     
        if ($stock <= 0) {
         return back()->with('error', 'No hay stock disponible para la talla seleccionada.');
        }


        try {
            DB::beginTransaction();

            // Obtener o crear inscripción
            $inscripcionId = session('inscripcion_id');
     
            if (!$inscripcionId) {

                $inscripcion = Inscripcion::create([
                    'created_by_id' => auth()->id(),
                ]);

       			session(['inscripcion_id' => $inscripcion->id]);
				$inscripcionId=$inscripcion->id;
        			
            } else { 

                  $inscripcion = Inscripcion::findOrFail($inscripcionId);
                 
            }
			
			  
            $data = $request->all();
            // Buscar categoría por edad
            $categoria = Categoria::where('edad_min', '<=', $edad)
                ->where(function ($q) use ($edad) {
                    $q->whereNull('edad_max')->orWhere('edad_max', '>=', $edad); 
                })
                ->first();

            $data['categoria'] =  $categoria->nombre;
            $data['tercera_edad'] = $edad >= 65 ? 1 : 0;
            $data['created_by_id'] = auth()->id();
			$data['inscripcion_id'] = $inscripcionId;

           //dd( $data);

            $participante = ParticipanteTemporal::create($data);
		
            DB::commit();

            return redirect()->route('admin.inscripciones.create')
                ->with('success', 'Participante añadido correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.inscripciones.index')
            ->with('flash_message', 'Ocurrió un error al guardar: ' . $e->getMessage());
        }
    }

public function resumen(Request $request)
{
    $inscripcionId = session('inscripcion_id');

    if (!$inscripcionId) {
        return redirect()->route('admin.inscripciones.create')
            ->with('error', 'No hay inscripción activa.');
    }

    $inscripcion = Inscripcion::with('participantes_temporal')->findOrFail($inscripcionId);

    
    if (!$inscripcion || $inscripcion->participantes_temporal->isEmpty()) {
        return redirect()->route('inscripciones.create')
            ->with('error', 'No hay participantes registrados en esta inscripción.');
    }

    $participantes = ParticipanteTemporal::with('tipoInscripcion')
            ->where('inscripcion_id', $inscripcionId)
            ->get();


    $primer = $participantes->first();
    $subtotal = 0;
        $iva_total = 0;
        foreach ($participantes as $p) {
            $valor = $p->tipoInscripcion->valor;
            //tercera_edad
            if($p->tercera_edad==1)
            {
                $valor= $valor/2;
            }    
            $iva=$valor-($valor/(1+$p->tipoInscripcion->iva));
            $valor=$valor-$iva;
            $subtotal += $valor;
            $iva_total += $iva;
        }
        $total = $subtotal+$iva_total;

$formasPago = FormaPago::where('estado', 'A')->get();
return view('inscripciones.resumen', compact(
    'inscripcion',
    'participantes',
    'subtotal',
    'iva_total',
    'total',
    'primer',
    'formasPago'
));


}


public function finalizar(Request $request)
{




////
    $inscripcionId = session('inscripcion_id');

   
         
    if (!$inscripcionId) {
        return redirect()->route('admin.inscripciones.create')->with('error', 'No hay inscripción activa.');
    }

  


        try {
            // Verificar si ya existe facturación para la inscripción
            $existing = Facturacion::where('inscripcion_id', $inscripcionId)->first();

           
            if ($existing) {
                return back()->with('error', 'Ya existe una factura asociada a esta inscripción.');
            }

            

            DB::beginTransaction();
            
            $temporales = ParticipanteTemporal::where('inscripcion_id', $inscripcionId)->get();
            $facturaTipo = $temporales->count() > 1 ? 'Mult' : 'Ind';   
            
                   
            foreach ($temporales as $temp) {
             $data = $temp->toArray();
             $data['factura'] = $facturaTipo;
             unset($data['id']); // Eliminar ID para evitar conflictos de duplicado
             $participante = Participante::create($data);
            }

           
           $participantes = Participante::with('tipoInscripcion')
            ->where('inscripcion_id', $inscripcionId)
            ->get();

            $subtotal = $participantes->sum(function ($p) {
            $valor = $p->tipoInscripcion->valor ?? 0;
            return $p->tercera_edad == 1 ? $valor / 2 : $valor;
            });

            $iva = $participantes->sum(function ($p) {
            $iva = $p->tipoInscripcion->iva ?? 0;
            return $p->tercera_edad == 1 ? $iva / 2 : $iva;
            });

            // Crear factura
            $factura =Facturacion::create ([
                'inscripcion_id'         => $inscripcionId ,
                'fact_tipo_documento'    => $request->fact_tipo_documento,
                'numero_doc_facturacion' => $request->numero_doc_facturacion,
                'nombre_facturacion'     => $request->nombre_facturacion,
                'apellido_facturacion'   => $request->apellido_facturacion,
                'email_facturacion'      => $request->email_facturacion,
                'telefono_facturacion'   => $request->telefono_facturacion,
                'direccion_facturacion'  => $request->direccion_facturacion,
                'nota_facturacion'       => $request->nota_facturacion,
                'valor'                  => $subtotal,
                'iva'                    => $iva,
                'pagado'                 => 1
            ]);

            

              // Crear pago principal

            $pagoPrincipal = Pago::create ([
            'inscripcion_id'  => $inscripcionId,
            'facturacion_id'  => $factura->id,
            'pago_id'         => $request->forma_pago,
            'total'           => $subtotal,
            'referencia'      => $request->referencia ?? null,
            'estado'          => 'PAGADO' // o 'PENDIENTE' si no está confirmado
            ]);

             // Actualizar estado de la inscripción
            Inscripcion::where('id', $inscripcionId)->update(['estado' => 1]);

            // Detalles por participante
            foreach ($participantes as $p) {
                
            $valor = ($p->tipoInscripcion->valor ?? 0) * ($p->tercera_edad == 1 ? 0.5 : 1);

                $detalle=FacturacionDetalle::create([
                    'facturacion_id' => $factura->id,
                    'participante_id' => $p->id,
                    'valor' => $valor,
                    'pagado' => 0
                    
                ]);

                $pago=PagoDetalle::create([
                'participante_id' => $p->id,
                'forma_pago_id' => $request->forma_pago, // debe ser ID de formas_pago
                'pagos_id'   => $pagoPrincipal->id,   // Relación con pago_principal
                'referencia'      => $request->referencia ?? null,
                'monto'           => $valor,
                'estado'          => 'A',
                'created_by_id'   => auth()->id(),
            ]);

            //descontar stock

            DB::table('inventario_total')
            ->where('talla', $p->talla)
            ->where('stock_restante', '>', 0)
            ->decrement('stock_restante', 1);

         
            }


        DB::commit();
        session()->forget('inscripcion_id');

        return redirect()->route('admin.inscripciones.index')->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error al finalizar: ' . $e->getMessage());
    }
}




}
