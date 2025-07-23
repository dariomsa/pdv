<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Models\Participante;
use App\Models\Pagos;
use App\Models\CierreCaja;
use App\Models\CierreCajaDetalle;
use App\Models\Inscripcion;


class CierreCajaController extends Controller
{
    public function index()
    {
        //abort_if(Gate::denies('cierre_caja_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
		// $cierrecaja = CierreCaja::select('cierre_caja.*', 'users.name')->join('users', 'cierre_caja.id_punto_servicio', 'users.id')->get();
		
		$fecha_desde = isset($_GET['fecha_desde']) &&  $_GET['fecha_desde'] != '' ? $_GET['fecha_desde'] : date('Y-m-d');
        $fecha_hasta = isset($_GET['fecha_hasta']) &&  $_GET['fecha_hasta'] != '' ? $_GET['fecha_hasta'] : date('Y-m-d');
		
		$isAdmin = auth()->user()->roles->contains(3);
		$isContabilidad = auth()->user()->roles->contains(5);

		if ($isAdmin || $isContabilidad) {				
			$cierrecaja = DB::table('cierre_caja')
			->selectRaw('cierre_caja.id, users.name, cierre_caja.fecha, cierre_caja.secuencia, cierre_caja.monto_total, cierre_caja.numero_facturas')
			->join('users', 'cierre_caja.id_punto_servicio', 'users.id')
			->whereBetween('cierre_caja.fecha',[$fecha_desde,$fecha_hasta])
			->get();
		} else {
			$cierrecaja = DB::table('cierre_caja')
			->selectRaw('cierre_caja.id, users.name, cierre_caja.fecha, cierre_caja.secuencia, cierre_caja.monto_total, cierre_caja.numero_facturas')
			->join('users', 'cierre_caja.id_punto_servicio', 'users.id')
			->where('cierre_caja.created_by_id', '=',auth()->user()->id)
			->whereBetween('cierre_caja.fecha',[$fecha_desde,$fecha_hasta])
			->get();		
		}
		
		return view('cierreCaja.index', compact('cierrecaja','fecha_desde', 'fecha_hasta'));
    }

    public function create()
    {
       // abort_if(Gate::denies('cierre_caja_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
		$datos = CierreCaja::all();

        return view('cierreCaja.create', compact('datos'));
    }
	

	public function store(Request $request)
{
    Session::forget('flash_message');

			$fecha_desde = isset($_GET['fecha_desde']) &&  $_GET['fecha_desde'] != '' ? $_GET['fecha_desde'] : date('Y-m-d');
        $fecha_hasta = isset($_GET['fecha_hasta']) &&  $_GET['fecha_hasta'] != '' ? $_GET['fecha_hasta'] : date('Y-m-d');

    $fechaSeleccionada = $request->input('fecha');

    // Obtener fechas de inscripciones activas sin cierre
    $fechasPendientes = DB::table('inscripciones')
        ->selectRaw('DATE(created_at) as fecha')
        ->where('id_cierre_caja', 0)
        ->where('estado', 1)
        ->groupBy(DB::raw('DATE(created_at)'))
        ->orderBy('fecha', 'desc')
        ->pluck('fecha')
        ->toArray();

    // Si no hay fechas pendientes
    if (empty($fechasPendientes)) {
        Session::flash('flash_message', 'No hay fechas pendientes de cierre.');
        return redirect()->back();
    }

    // Validar si la fecha enviada está entre las fechas pendientes
    if (!in_array($fechaSeleccionada, $fechasPendientes)) {
        $fechasTexto = implode(', ', $fechasPendientes);
        Session::flash('flash_message', 'La fecha seleccionada no es válida. Fechas pendientes: ' . $fechasTexto);
        return redirect()->back();
    }

    $fecha = $request->input('fecha');
    $userId = auth()->id();
	
		// Obtener resumen general
     $facturas = DB::table('inscripciones')
    ->join('pagos', 'inscripciones.id', '=', 'pagos.inscripcion_id')
    ->where('inscripciones.estado', 1)
    ->where('inscripciones.id_cierre_caja', 0)
    ->whereDate('inscripciones.created_at', $fecha)
    ->where('inscripciones.created_by_id', $userId)
    ->selectRaw('COUNT(inscripciones.id) as numero_facturas, SUM(pagos.total) as monto_total')
    ->first();



		
		// Obtener detalle por forma de pago
    $detalle_facturas = DB::table('inscripciones')
    ->join('pagos', 'inscripciones.id', '=', 'pagos.inscripcion_id')
    ->where('inscripciones.estado', 1)
    ->where('inscripciones.id_cierre_caja', 0)
    ->whereDate('inscripciones.created_at', $fecha)
    ->where('inscripciones.created_by_id', $userId)
    ->selectRaw('COUNT(inscripciones.id) as numero_facturas_det, SUM(pagos.total) as monto_total_det, pagos.pago_id')
    ->groupBy('pagos.pago_id')
    ->get();

			
// Obtener próxima secuencia
$ultimaSecuencia = CierreCaja::where('id_punto_servicio', $userId)
    ->whereDate('fecha', $fecha)
    ->max('secuencia');

$secuencia = $ultimaSecuencia ? $ultimaSecuencia + 1 : 1;
		
		try {
			DB::beginTransaction();
			
	

			$caja = CierreCaja::create([
    'fecha'            => $fecha,
    'secuencia'        => $secuencia,
    'id_punto_servicio'=> $userId,
    'monto_total'      => $facturas->monto_total,
    'numero_facturas'  => $facturas->numero_facturas,
    'created_by_id'    => $userId
]);

			

 // Insertar detalles por forma de pago
foreach ($detalle_facturas as $detalle) {
    CierreCajaDetalle::create([
        'cierre_caja_id'   => $caja->id,
        'forma_pago_id'    => $detalle->pago_id,
        'numero_facturas'  => $detalle->numero_facturas_det,
        'monto'            => $detalle->monto_total_det,
        'created_by_id'    => $userId
    ]);
}

$inscripciones = DB::table('inscripciones')
    ->where('estado', 1)
    ->where('id_cierre_caja', 0)
    ->whereDate('created_at', $fecha)
    ->where('created_by_id', $userId)
    ->pluck('id');

DB::table('inscripciones')
    ->whereIn('id', $inscripciones)
    ->update(['id_cierre_caja' => $caja->id]);


			DB::commit();

			
        } catch (\Exception $e) {
            DB::rollBack();
            dd ('Error en el sistema', $e);
        }

		$cierrecaja = CierreCaja::select('cierre_caja.*', 'users.name')->join('users', 'cierre_caja.id_punto_servicio', 'users.id')->get();
		return view('cierreCaja.index', compact('cierrecaja', 'fecha_desde', 'fecha_hasta'));
	}
	
	public function show($id)
    {
		//abort_if(Gate::denies('cierre_caja_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');	
		$caja = DB::table('cierre_caja')->where('id', $id)->first();
		$detalle = DB::table('cierre_caja_detalles')->where('cierre_caja_id',$id)->get();
		$facturas = Inscripcion::where('id_cierre_caja',$id)->get();
		$puntoventa = User::find($caja->id_punto_servicio);

		return view('cierreCaja.show', compact('caja', 'detalle','facturas', 'puntoventa'));
    }
	
	
}