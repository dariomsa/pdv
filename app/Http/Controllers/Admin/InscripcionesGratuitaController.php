<?php

namespace App\Http\Controllers\Admin;

use App\Models\InscripcionGratuita;
use App\Models\ParticipanteGratuita;
use App\Models\ParticipanteTemporalGratuita;
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




class InscripcionesGratuitaController extends Controller
{
  



public function index(Request $request)
{
    
    session()->forget('inscripcion_id');

    $fecha_desde = $request->input('fecha_desde', date('Y-m-d'));
    $fecha_hasta = $request->input('fecha_hasta', date('Y-m-d'));

    $participantes = ParticipanteGratuita::with('tipoInscripcion')
        ->when(!tieneRol('Administrador_oficina'), function ($query) {
            $query->where('created_by_id', Auth::id());
        })
        ->when($request->filled('fecha_desde'), function ($query) use ($fecha_desde) {
            $query->whereDate('created_at', '>=', $fecha_desde);
        })
        ->when($request->filled('fecha_hasta'), function ($query) use ($fecha_hasta) {
            $query->whereDate('created_at', '<=', $fecha_hasta);
        })
        ->get()
        ->map(function ($p) {
            return (object) [
                'id' => $p->id,
                'created_at' => $p->created_at,
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
                'metodo_pago' => 'NO APLICA',
                'referencia' => 'NO APLICA',
                'sub_total' => '0.00',
                'iva' => '0.00',
                'total' => '0.00',
                'discapacidad' => 'ND',
            ];
        });

    return view('inscripciones_gratuitas.index', compact('participantes', 'fecha_desde', 'fecha_hasta'));
}


    public function create(Request $request)
    {
        $tallasDisponibles = DB::table('inventario_total')
        ->where('stock_restante', '>', 0)
        ->orderBy('id')
        ->get();
        $paises = Pais::orderBy('id')->get();
        $inscripcionId = $request->session()->get('inscripcion_id');
        return view('inscripciones_gratuitas.create', compact('inscripcionId','paises','tallasDisponibles'));
    }

    Public function store(Request $request)
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

    // Verificar stock de camisetas
    $stock = DB::table('inventario_total')
        ->where('talla', $request->talla)
        ->value('stock_restante');

    if ($stock <= 0) {
        return back()->with('error', 'No hay stock disponible para la talla seleccionada.');
    }

    try {
        DB::beginTransaction();

        // Obtener o crear inscripción gratuita
        $inscripcionId = session('inscripcion_id');

        if (!$inscripcionId) {
            $inscripcion = InscripcionGratuita::create([
                'created_by_id' => auth()->id(),
            ]);

            session(['inscripcion_id' => $inscripcion->id]);
            $inscripcionId = $inscripcion->id;
        } else {
            $inscripcion = InscripcionGratuita::findOrFail($inscripcionId);
        }

        // Preparar datos
        $data = $request->all();

        $fechaNacimiento = \Carbon\Carbon::parse($request->fecha_nacimiento);
        $edad = $fechaNacimiento->age;

        $categoria = Categoria::where('edad_min', '<=', $edad)
            ->where(function ($q) use ($edad) {
                $q->whereNull('edad_max')->orWhere('edad_max', '>=', $edad);
            })
            ->first();

        $data['categoria'] = $categoria ? $categoria->nombre : 'Sin categoría';
        $data['tercera_edad'] = $edad >= 65 ? 1 : 0;
        $data['created_by_id'] = auth()->id();
        $data['inscripcion_id'] = $inscripcionId;

        ParticipanteTemporalGratuita::create($data);

        DB::commit();

        return redirect()->route('admin.inscripciones_gratuitas.create')
            ->with('success', 'Participante añadido correctamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('admin.inscripciones_gratuitas.index')
            ->with('flash_message', 'Ocurrió un error al guardar: ' . $e->getMessage());
    }
}

  
public function resumen(Request $request)
{
    $inscripcionId = session('inscripcion_id');

    if (!$inscripcionId) {
        return redirect()->route('admin.inscripciones_gratuitas.create')
            ->with('error', 'No hay inscripción activa.');
    }

    $inscripcion = InscripcionGratuita::with('participantes_temporal')->findOrFail($inscripcionId);

    if (!$inscripcion || $inscripcion->participantes_temporal->isEmpty()) {
        return redirect()->route('admin.inscripciones_gratuitas.create')
            ->with('error', 'No hay participantes registrados en esta inscripción.');
    }

    $participantes = ParticipanteTemporalGratuita::with('tipoInscripcion')
        ->where('inscripcion_id', $inscripcionId)
        ->get();

    $primer = $participantes->first();
    $subtotal = 0;
    $iva_total = 0;
    $total = 0;

    return view('inscripciones_gratuitas.resumen', compact(
        'inscripcion',
        'participantes',
        'subtotal',
        'iva_total',
        'total',
        'primer'
    ));
}


public function finalizar(Request $request)
{
    $inscripcionId = session('inscripcion_id');

  

    if (!$inscripcionId) {
        return redirect()->route('admin.inscripciones_gratuitas.create')
            ->with('error', 'No hay inscripción activa.');
    }

    try {
        DB::beginTransaction();

        $temporales = ParticipanteTemporalGratuita::where('inscripcion_id', $inscripcionId)->get();
        $facturaTipo = $temporales->count() > 1 ? 'Mult' : 'Ind';    

         

        if ($temporales->isEmpty()) {
            return redirect()->route('admin.inscripciones_gratuitas.create')
                ->with('error', 'No hay participantes registrados en esta inscripción.');
        }

        foreach ($temporales as $temp) {
             $data = $temp->toArray();
             $data['factura'] = $facturaTipo;
             unset($data['id']); // Eliminar ID para evitar conflictos de duplicado
             $participante = ParticipanteGratuita::create($data);

             dd($participante);

            // descontar stock
            DB::table('inventario_total')
                ->where('talla', $participante->talla)
                ->where('stock_restante', '>', 0)
                ->decrement('stock_restante', 1);
        }

        // Actualizar estado de la inscripción
        InscripcionGratuita::where('id', $inscripcionId)->update(['estado' => 1]);

        DB::commit();
        session()->forget('inscripcion_id');

        return redirect()->route('admin.inscripciones_gratuitas.index')
            ->with('success', 'Inscripción gratuita completada exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error al finalizar: ' . $e->getMessage());
    }
}



}
