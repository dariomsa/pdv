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
use App\Models\InventarioTotal;
use Illuminate\Support\Facades\Auth;
use App\helpers\ParticipanteHelper;
use Illuminate\Support\Facades\Log;



use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;



class InscripcionesController extends Controller
{
    


public function index(Request $request)
{
    session()->forget('inscripcion_id');

    $hoy = date('Y-m-d');

    $fecha_desde = $request->filled('fecha_desde') ? $request->fecha_desde : $hoy;
    $fecha_hasta = $request->filled('fecha_hasta') ? $request->fecha_hasta : $hoy;

    // carrera_id (1=15K, 2=21K, etc.)
    $carrera = $request->input('carrera');

    $participantes = Participante::with(['tipoInscripcion'])
        ->when(!tieneRol('Administrador_oficina'), function ($query) {
            $query->where('created_by_id', Auth::id());
        })
        // ✅ Filtro por carrera_id en inscripcion_tipo
        ->when(!empty($carrera), function ($query) use ($carrera) {
            $query->whereHas('tipoInscripcion', function ($q) use ($carrera) {
                $q->where('carrera_id', $carrera);
            });
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
                'factura_numero' => $factura ? substr($factura->clave_acceso, 24, 15) : 'ND',
                'empresa_factura' => $factura->nombre_facturacion ?? 'ND',
                'telefono_factura' => $factura->telefono_facturacion ?? 'ND',
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
                'discapacidad' => 'ND',
            ];
        });

    return view('inscripciones.index', compact('participantes', 'fecha_desde', 'fecha_hasta', 'carrera'));
}


public function create(Request $request)
{
    $user = auth()->user();

    $tallasDisponibles = DB::table('inventario_total')
        ->where('stock_restante', '>', 0)
        ->orderBy('id')
        ->get();

    $paises = Pais::orderBy('id')->get();

    // ✅ TIPOS DE INSCRIPCIÓN SEGÚN USUARIO
    $tiposInscripcion = DB::table('inscripcion_tipo')
        ->when($user->id != 10, function ($q) {
            // 👉 SOLO ACTIVOS para todos menos el usuario 10
            $q->where('estado', 1);
        })
        ->orderBy('id')
        ->get();

    return view('inscripciones.create', compact(
        'paises',
        'tallasDisponibles',
        'tiposInscripcion'
    ));
}


  public function store(Request $request)
{
    if (!auth()->check()) {
        return redirect()->route('login')->with('error', 'Debe iniciar sesión para realizar la inscripción.');
    }

    $request->validate([
        'tipo_inscripcion'   => 'required|integer',
        'tipo_documento'     => 'required|string',
        'numero_documento'   => 'required|string|max:15',
        'nombres'            => 'required|string',
        'apellidos'          => 'required|string',
        'genero'             => 'required|in:M,F',
        'fecha_nacimiento'   => 'required|date',
        'nacionalidad'       => 'required|string',
        'celular'            => 'required|string|max:10',
        'talla'              => 'required',
        // los subtipos se validan abajo condicionalmente
    ]);

    // ✅ Si ya está inscrito
    if (ParticipanteHelper::yaInscrito($request->numero_documento)) {
        return redirect()->back()
            ->with('success', 'El participante ' . $request->numero_documento . ' ya está inscrito en otra modalidad.');
    }

    // ✅ Tipo inscripción -> carrera
    $tipoInscripcionActual = InscripcionTipo::findOrFail($request->tipo_inscripcion);
    $carreraId = (int) $tipoInscripcionActual->carrera_id; // 1=15K,2=21K,3=5K

    // ✅ Discapacidad según tipo de inscripción (según tu Blade: 4 => conadis15k, 8 => conadis21k)
    $tipo = (int) $request->tipo_inscripcion;
    $esDiscapacidad15k = ($tipo === 4);
    $esDiscapacidad21k = ($tipo === 9);

    // ✅ Validación condicional de subtipo
    if ($esDiscapacidad15k) {
        $request->validate([
            'subtipo_conadis15k' => 'required|string|in:COMPETENCIA,PASEO,HANDCYCLE,OTROS',
        ]);
    }

    if ($esDiscapacidad21k) {
        $request->validate([
            'subtipo_conadis21k' => 'required|string|in:OTROS',
        ]);
    }

    // ✅ Stock (MEJOR: talla + carrera + genero)
    $stock = DB::table('inventario_total')
        ->where('carrera_id', $carreraId)
        ->where('genero', $request->genero)
        ->where('talla', $request->talla)
        ->value('stock_restante');

    if ((int)$stock <= 0) {
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
            $inscripcionId = $inscripcion->id;
        } else {
            $inscripcion = Inscripcion::findOrFail($inscripcionId);
        }

        // Calcular edad y categoría
        $fechaNacimiento = \Carbon\Carbon::parse($request->fecha_nacimiento);
        $edad = $fechaNacimiento->age;

        $categoria = Categoria::where('carrera_id', $carreraId)
            ->where('edad_min', '<=', $edad)
            ->where(function ($q) use ($edad) {
                $q->whereNull('edad_max')
                  ->orWhere('edad_max', '>=', $edad);
            })
            ->first();

        // ✅ Preparar data (solo lo que necesitas)
        $data = $request->all();

        // Guardar categoría si corresponde
        if ($categoria) {
            $data['categoria'] = $categoria->nombre ?? ($categoria->categoria ?? null);
        }

        // ✅ Discapacidad
        $data['discapacidad'] = ($esDiscapacidad15k || $esDiscapacidad21k) ? 1 : 0;

        // (Opcional) guardar el subtipo
        if ($esDiscapacidad15k) {
            $data['sub_tipo'] = $request->subtipo_conadis15k;
        } elseif ($esDiscapacidad21k) {
            $data['sub_tipo'] = $request->subtipo_conadis21k;
        } else {
            $data['sub_tipo'] = null;
        }

        $data['tercera_edad'] = $edad >= 65 ? 1 : 0;
        $data['created_by_id'] = auth()->id();
        $data['inscripcion_id'] = $inscripcionId;

        \Log::info("data: " . json_encode($data));

        $participante = ParticipanteTemporal::create($data);

        \Log::info("participante: " . json_encode($participante));

        // (Opcional recomendado) descontar stock aquí si tu lógica lo requiere al "Continuar"
        // DB::table('inventario_total')
        //   ->where('carrera_id',$carreraId)->where('genero',$request->genero)->where('talla',$request->talla)
        //   ->decrement('stock_restante', 1);

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
            if ((int)$p->tercera_edad === 1 && (int)($p->discapacidad ?? 0) !== 1) {
            $valor = $valor / 2;
            }

			
            $iva=$valor-($valor/(1+$p->tipoInscripcion->iva));
            $valor=$valor-$iva;
            $subtotal += $valor;
            $iva_total += $iva;
        }
        $total = $subtotal+$iva_total;


$user = auth()->user();

$formasPago = FormaPago::when($user->id != 10, function ($q) {
        $q->where('estado', 'A');
    })
    ->get();


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


public function gratuita(Request $request)
{
    $inscripcionId = session('inscripcion_id');
	
	//dd( $inscripcionId);

    if (!$inscripcionId) {
        return redirect()->route('admin.inscripciones.create')
            ->with('error', 'No hay inscripción activa.');
    }

    try {

        $existing = Facturacion::where('inscripcion_id', $inscripcionId)->first();
        if ($existing) {
            return back()->with('error', 'Ya existe una factura asociada a esta inscripción.');
        }

        DB::beginTransaction();

        // Pasar temporales a participantes definitivos
        $temporales = ParticipanteTemporal::where('inscripcion_id', $inscripcionId)->get();
        $facturaTipo = $temporales->count() > 1 ? 'Mult' : 'Ind';

        foreach ($temporales as $temp) {
            $data = $temp->toArray();
            $data['factura'] = $facturaTipo;
            unset($data['id']);
            Participante::create($data);
        }

        // Obtener participantes definitivos
        $participantes = Participante::with('tipoInscripcion')
            ->where('inscripcion_id', $inscripcionId)
            ->get();

        // 🔴 TODO A CERO
        $subtotal = 0;
        $iva = 0;

        // Crear factura (igual que finalizar)
        $factura = Facturacion::create([
            'inscripcion_id'         => $inscripcionId,
            'fact_tipo_documento'    => $request->fact_tipo_documento,
            'numero_doc_facturacion' => $request->numero_doc_facturacion,
            'nombre_facturacion'     => $request->nombre_facturacion.' '.$request->apellido_facturacion,
            'apellido_facturacion'   => $request->nombre_facturacion.' '.$request->apellido_facturacion,
            'email_facturacion'      => $request->email_facturacion,
            'telefono_facturacion'   => $request->telefono_facturacion,
            'direccion_facturacion'  => $request->direccion_facturacion,
            'nota_facturacion'       => 'INSCRIPCION GRATUITA',
			'enviado_facturacion'    => 1,
            'valor'                  => 0,
            'iva'                    => 0,
            'pagado'                 => 0
        ]);

        // Crear pago principal
        $pagoPrincipal = Pago::create([
            'inscripcion_id' => $inscripcionId,
            'facturacion_id' => $factura->id,
            'pago_id'        => $request->forma_pago,
            'total'          => 0,
            'referencia'     => $request->referencia ?? null,
            'estado'         => 'PAGADO'
        ]);

        // Actualizar estado inscripción
        Inscripcion::where('id', $inscripcionId)->update([
            'estado' => 1
        ]);

        // Detalles + pago detalle + descuento stock
        foreach ($participantes as $p) {

            $valor = 0; // 🔴 TODO EN 0

            FacturacionDetalle::create([
                'facturacion_id'  => $factura->id,
                'participante_id' => $p->id,
                'valor'           => 0,
                'pagado'          => 0
            ]);

            PagoDetalle::create([
                'participante_id' => $p->id,
                'forma_pago_id'   => $request->forma_pago,
                'pagos_id'        => $pagoPrincipal->id,
                'referencia'      => $request->referencia ?? null,
                'monto'           => 0,
                'estado'          => 'A',
                'created_by_id'   => auth()->id(),
            ]);

            // 🔵 Stock se descuenta igual
            DB::table('inventario_total')
                ->where('carrera_id', (int)($p->tipoInscripcion->carrera_id ?? 0))
                ->where('genero', $p->genero)
                ->where('talla', $p->talla)
                ->where('stock_restante', '>', 0)
                ->decrement('stock_restante', 1);
        }

        DB::commit();

  

        session()->forget('inscripcion_id');

        return redirect()->route('admin.inscripciones.index')
       ->with('success', 'Inscripción GRATUITA completada exitosamente.');

    } catch (\Exception $e) {

        DB::rollBack();

        return redirect()->back()
            ->with('error', 'Error al finalizar gratuita: ' . $e->getMessage());
    }
}




public function finalizar(Request $request)
{
    $inscripcionId = session('inscripcion_id');

    if (!$inscripcionId) {
        return redirect()->route('admin.inscripciones.create')
            ->with('error', 'No hay inscripción activa.');
    }

    try {
        // Verificar si ya existe facturación para la inscripción
        $existing = Facturacion::where('inscripcion_id', $inscripcionId)->first();
        if ($existing) {
            return back()->with('error', 'Ya existe una factura asociada a esta inscripción.');
        }

        DB::beginTransaction();

        // Pasar temporales a participantes definitivos
        $temporales = ParticipanteTemporal::where('inscripcion_id', $inscripcionId)->get();
        $facturaTipo = $temporales->count() > 1 ? 'Mult' : 'Ind';

        foreach ($temporales as $temp) {
            $data = $temp->toArray();
            $data['factura'] = $facturaTipo;
            unset($data['id']); // evitar duplicados
            Participante::create($data);
        }

        // Obtener participantes definitivos con tipoInscripcion
        $participantes = Participante::with('tipoInscripcion')
            ->where('inscripcion_id', $inscripcionId)
            ->get();

        // ✅ SUBTOTAL: aplica 50% solo si tercera_edad=1 y NO discapacidad
        $subtotal = $participantes->sum(function ($p) {
            $valor = (float)($p->tipoInscripcion->valor ?? 0);

            $aplicaTerceraEdad = ((int)$p->tercera_edad === 1) && ((int)($p->discapacidad ?? 0) !== 1);

            return $aplicaTerceraEdad ? ($valor / 2) : $valor;
        });

  
        $iva = $participantes->sum(function ($p) {
            $iva = (float)($p->tipoInscripcion->iva ?? 0);

            $aplicaTerceraEdad = ((int)$p->tercera_edad === 1) && ((int)($p->discapacidad ?? 0) !== 1);

            return $aplicaTerceraEdad ? ($iva / 2) : $iva;
        });

        // Crear factura
        $factura = Facturacion::create([
            'inscripcion_id'         => $inscripcionId,
            'fact_tipo_documento'    => $request->fact_tipo_documento,
            'numero_doc_facturacion' => $request->numero_doc_facturacion,
            'nombre_facturacion'     => $request->nombre_facturacion.' '.$request->apellido_facturacion,
            'apellido_facturacion'   => $request->nombre_facturacion.' '.$request->apellido_facturacion,
            'email_facturacion'      => $request->email_facturacion,
            'telefono_facturacion'   => $request->telefono_facturacion,
            'direccion_facturacion'  => $request->direccion_facturacion,
            'nota_facturacion'       => $request->nota_facturacion,
            'valor'                  => $subtotal,
            'iva'                    => $iva,
            'pagado'                 => 1
        ]);

        // Crear pago principal
        $pagoPrincipal = Pago::create([
            'inscripcion_id' => $inscripcionId,
            'facturacion_id' => $factura->id,
            'pago_id'        => $request->forma_pago,
            'total'          => $subtotal,
            'referencia'     => $request->referencia ?? null,
            'estado'         => 'PAGADO'
        ]);

        // Actualizar estado de la inscripción
        Inscripcion::where('id', $inscripcionId)->update(['estado' => 1]);

        // Detalles por participante + pago detalle + descuento stock
        foreach ($participantes as $p) {

            $valorBase = (float)($p->tipoInscripcion->valor ?? 0);

            $aplicaTerceraEdad = ((int)$p->tercera_edad === 1) && ((int)($p->discapacidad ?? 0) !== 1);
            $valor = $aplicaTerceraEdad ? ($valorBase * 0.5) : $valorBase;

            FacturacionDetalle::create([
                'facturacion_id'  => $factura->id,
                'participante_id' => $p->id,
                'valor'           => $valor,
                'pagado'          => 0
            ]);

            PagoDetalle::create([
                'participante_id' => $p->id,
                'forma_pago_id'   => $request->forma_pago,
                'pagos_id'        => $pagoPrincipal->id,
                'referencia'      => $request->referencia ?? null,
                'monto'           => $valor,
                'estado'          => 'A',
                'created_by_id'   => auth()->id(),
            ]);

 
            DB::table('inventario_total')
                ->where('carrera_id', (int)($p->tipoInscripcion->carrera_id ?? 0))
                ->where('genero', $p->genero)
                ->where('talla', $p->talla)
                ->where('stock_restante', '>', 0)
                ->decrement('stock_restante', 1);
        }

        DB::commit();

    DB::statement("
        UPDATE participantes p
        JOIN base2024 b
            ON p.numero_documento = b.cedula
        SET p.corral = TRIM(b.corral) WHERE p.id >9410
    ");
		
		session(['recibo' => $p->id]);

        session()->forget('inscripcion_id');

        return redirect()->route('admin.inscripciones.index')
            ->with('success', 'Inscripción completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error al finalizar: ' . $e->getMessage());
    }
}



public function updateInline(Request $request)
{
    $id     = $request->input('id');
    $column = $request->input('column');
    $value  = $request->input('value'); // puede venir null

    // Whitelist: SOLO estos campos son editables por ahora
    $allowed = [
        'nombres',
        'apellidos',
        'genero',
        'corral',
        'categoria',
        'fecha_nacimiento',
        'talla',
        'email',
    ];

    if (!$id || !$column || !in_array($column, $allowed, true)) {
        return response()->json([
            'success' => false,
            'message' => 'Campo no editable o datos incompletos',
        ], 422);
    }

    $participante = Participante::find($id);
    if (!$participante) {
        return response()->json([
            'success' => false,
            'message' => 'Registro no encontrado',
        ], 404);
    }

    // Normalizaciones previas
    $normValue = is_null($value) ? null : trim($value);

    // Conjuntos permitidos (ajusta si usas otros valores)
    $generosPermitidos = ['M', 'F', 'O']; // O = otro/opcional
    $corralesPermitidos = ['CORRAL A', 'CORRAL B', 'CORRAL C', 'CORRAL D', 'ND'];
    $tallasPermitidas = ['XS','S','M','L','XL','XXL','ND'];

    // Reglas por campo
    $rules = [];
    switch ($column) {
        case 'nombres':
        case 'apellidos':
        case 'categoria':
            $rules[$column] = ['nullable', 'string', 'max:191'];
            // Capitaliza cada palabra
          
            break;

        case 'genero':
            $rules[$column] = ['nullable', 'in:' . implode(',', $generosPermitidos)];
            if (!is_null($normValue)) $normValue = Str::upper($normValue);
            break;

        case 'corral':
            $rules[$column] = ['nullable', 'in:' . implode(',', $corralesPermitidos)];
            if (!is_null($normValue)) $normValue = Str::upper($normValue);
            break;

        case 'talla':
            $rules[$column] = ['nullable', 'in:' . implode(',', $tallasPermitidas)];
            if (!is_null($normValue)) $normValue = Str::upper($normValue);
            break;

        case 'email':
            $rules[$column] = ['nullable', 'email', 'max:191'];
            $normValue = $normValue ? Str::lower($normValue) : null;
            break;

        case 'fecha_nacimiento':
            // Acepta Y-m-d o d/m/Y; normaliza a Y-m-d
            if (!empty($normValue)) {
                $parsed = null;
                foreach (['Y-m-d','d/m/Y'] as $fmt) {
                    try {
                        $parsed = Carbon::createFromFormat($fmt, $normValue);
                        if ($parsed !== false) break;
                    } catch (\Exception $e) { /* ignora */ }
                }
                if (!$parsed) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato de fecha inválido. Use YYYY-MM-DD o DD/MM/YYYY.',
                    ], 422);
                }
                $normValue = $parsed->format('Y-m-d');
            } else {
                $normValue = null;
            }
            $rules[$column] = ['nullable', 'date'];
            break;
    }

    // Validar
    $v = Validator::make([$column => $normValue], $rules);
    if ($v->fails()) {
        return response()->json([
            'success' => false,
            'message' => $v->errors()->first(),
        ], 422);
    }

    // Guardar
    $participante->{$column} = $normValue;
    $participante->save();

    return response()->json([
        'success' => true,
        'value'   => $normValue, // se devuelve el valor normalizado para re-pintar la celda
    ]);
}




public function eliminarInscripcionCompleta(Request $request)
{
    $request->validate([
        'inscripcion_id' => 'required|integer|exists:inscripciones,id',
    ]);

    $inscripcionId = (int) $request->inscripcion_id;

    try {
        DB::beginTransaction();

        // 1) Traer IDs de participantes de esa inscripción
        $participanteIds = DB::table('participantes')
            ->where('inscripcion_id', $inscripcionId)
            ->pluck('id');

        // 2) Borrar pagos_detalle (por participante_id)
        if ($participanteIds->isNotEmpty()) {
            DB::table('pagos_detalle')
                ->whereIn('participante_id', $participanteIds)
                ->delete();

            // 3) Borrar facturacion_detalle (por participante_id)
            DB::table('facturacion_detalle')
                ->whereIn('participante_id', $participanteIds)
                ->delete();
        }

        // 4) Borrar participantes
        DB::table('participantes')
            ->where('inscripcion_id', $inscripcionId)
            ->delete();

        // 5) Borrar pagos (pago principal)
        DB::table('pagos')
            ->where('inscripcion_id', $inscripcionId)
            ->delete();

        // 6) Borrar facturacion
        DB::table('facturacion')
            ->where('inscripcion_id', $inscripcionId)
            ->delete();

        // 7) Borrar inscripciones
        DB::table('inscripciones')
            ->where('id', $inscripcionId)
            ->delete();

        DB::commit();

        Log::info("Inscripción eliminada completa (sin stock)", [
            'inscripcion_id' => $inscripcionId,
            'participantes_borrados' => $participanteIds->count(),
        ]);

        return response()->json([
            'status' => 'ok',
            'mensaje' => 'Inscripción eliminada correctamente',
            'inscripcion_id' => $inscripcionId,
            'participantes_borrados' => $participanteIds->count(),
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error("Error eliminando inscripción completa", [
            'inscripcion_id' => $inscripcionId,
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => 'error',
            'mensaje' => 'Error interno al eliminar la inscripción.',
        ], 500);
    }
}

public function tallasDisponibles(Request $request)
{
    $request->validate([
        'carrera_id' => 'required|integer',
        'genero'     => 'required|in:M,F',
    ]);

    $carreraId = (int) $request->carrera_id;
    $genero    = $request->genero;

    // Si tienes stock_restante:
    $tallas = InventarioTotal::query()
        ->where('carrera_id', $carreraId)
        ->where('genero', $genero)
        ->where('stock_restante', '>', 0)
        ->orderByRaw("FIELD(talla,'XS','S','M','L','XL')")
        ->pluck('talla')
        ->values();

    return response()->json($tallas);
}




}
