<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\User;
use App\Inscripcion;
use App\Pagos;
use App\CierreCaja;
use App\CierreCajaDetalle;


class CajaDetalleController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('cierre_caja_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
		
		
		
		
		$cierrecaja = DB::select('
			SELECT cierre_caja.`id`,users.`name`,cierre_caja.fecha,cierre_caja.`secuencia`,
			tmps.`tipo_inscripcion`,facturacions.`valor`,facturacions.`clave_accesso`,
			formas_pago.`metodo_pago`,pagos.`referencia`,pagos.`monto`
			FROM `cierre_caja` 
			INNER JOIN tmps 
			INNER JOIN `facturacions` 
			INNER JOIN `pagos`
			INNER JOIN users
			INNER JOIN `formas_pago`
			WHERE cierre_caja.`id`=tmps.`id_cierre_caja`
			AND facturacions.`id_inscrito`=tmps.id
			AND pagos.`id_inscripcion`=tmps.id
			AND users.id=tmps.`created_by_id`
			AND `formas_pago`.id=pagos.`id_pago`
AND tmps.created_by_id='.auth()->user()->id.' 
ORDER BY cierre_caja.id DESC
			')	;
			
		
		$fecha_desde = isset($_GET['fecha_desde']) &&  $_GET['fecha_desde'] != '' ? $_GET['fecha_desde'] : date('Y-m-d');
        $fecha_hasta = isset($_GET['fecha_hasta']) &&  $_GET['fecha_hasta'] != '' ? $_GET['fecha_hasta'] : date('Y-m-d');
		
		$isAdmin = auth()->user()->roles->contains(3);
		$isContabilidad = auth()->user()->roles->contains(5);

		if ($isAdmin || $isContabilidad) {				
			$cierrecaja = DB::select('
			SELECT cierre_caja.`id`,users.`name`,cierre_caja.fecha,cierre_caja.`secuencia`,
			tmps.`tipo_inscripcion`,facturacions.`valor`,facturacions.`clave_accesso`,
			formas_pago.`metodo_pago`,pagos.`referencia`,pagos.`monto`
			FROM `cierre_caja` 
			INNER JOIN tmps 
			INNER JOIN `facturacions` 
			INNER JOIN `pagos`
			INNER JOIN users
			INNER JOIN `formas_pago`
			WHERE cierre_caja.`id`=tmps.`id_cierre_caja`
			AND facturacions.`id_inscrito`=tmps.id
			AND pagos.`id_inscripcion`=tmps.id
			AND users.id=tmps.`created_by_id`
			AND `formas_pago`.id=pagos.`id_pago`
AND cierre_caja.`fecha` BETWEEN "'.$fecha_desde.'" AND "'.$fecha_hasta.'" 
ORDER BY cierre_caja.id DESC
			')
			;

			//dd($cierrecaja);
		} else {
					
		}
		
		return view('admin.detalleCaja.index', compact('cierrecaja','fecha_desde', 'fecha_hasta'));
    }

    public function create()
    {
        abort_if(Gate::denies('cierre_caja_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');
		$datos = CierreCaja::all();

        return view('admin.cierreCaja.create', compact('datos'));
    }
	
	public function store(Request $request)
	{
		// dd(auth()->user()->id);
		Session::forget('flash_message');

		$listaFechasRequest = array();
		$fechasRequest = "";
		
		// Verificar fechas pendientes de cierre anteriores
		$fechasPendientes = DB::table('tmps')
				->selectRaw('date(tmps.created_at) as fecha')
				->join('pagos', 'tmps.id','=',  'pagos.id_inscripcion')
				->whereNull('tmps.deleted_at')
				->where('tmps.pagado', '=', 1)
				// ->where('tmps.facturado', '=', 1)
				->where('tmps.id_cierre_caja', '=', 0)
				->where('pagos.estado', '=','A')
				->whereDate('tmps.created_at', '<', $request->fecha)
				->where('tmps.created_by_id', '=',auth()->user()->id)
				->distinct()
				->orderBy('tmps.created_at', 'asc')
				->get();		


		if (count($fechasPendientes) != 0) {
			foreach ($fechasPendientes as $f) {
				array_push($listaFechasRequest, $f);
			}
			
			$numAb = count($listaFechasRequest);
			
			if ($numAb > 0) {
				foreach ($listaFechasRequest as $orAb) {
					$fechasRequest = $fechasRequest . $orAb->fecha . ',  ';
				}
				Session::flash('flash_message', '
					Existen fechas pendientes de cierre: ' . $fechasRequest);
				$datos = CierreCaja::all();

				return view('admin.cierreCaja.create', compact('datos'));				
			}
		}

		// Revisar si existen inscripciones para realizar cierre de caja
		$tmps = DB::table('tmps')
				->select('tmps.*')
				->join('pagos', 'tmps.id','=',  'pagos.id_inscripcion')
				->whereNull('tmps.deleted_at')
				->where('tmps.pagado', '=', 1)
				// ->where('tmps.facturado', '=', 1)
				->where('tmps.id_cierre_caja', '=', 0)
				->where('pagos.estado', '=','A')
				->whereDate('tmps.created_at', '=', $request->fecha)
				->where('tmps.created_by_id', '=',auth()->user()->id)
				->get();
			
		$numRegistros = count($tmps);
			
		if($numRegistros==0) {
				
			Session::flash('flash_message', '
				No existen inscripciones a cerrar para esta fecha');
			$datos = CierreCaja::all();

			return view('admin.cierreCaja.create', compact('datos'));
		}
		
		$facturas = DB::table('tmps')
				->selectRaw('count(tmps.id) as numero_facturas, sum(pagos.monto) as monto_total')
				->join('pagos', 'tmps.id','=',  'pagos.id_inscripcion')
				->whereNull('tmps.deleted_at')
				->where('tmps.pagado', '=', 1)
				// ->where('tmps.facturado', '=', 1)
				->where('tmps.id_cierre_caja', '=', 0)
				->where('pagos.estado', '=','A')
				->whereDate('tmps.created_at', '=', $request->fecha)
				->where('tmps.created_by_id', '=',auth()->user()->id)
				->first();

		
		$detalle_facturas = DB::table('tmps')
				->selectRaw('count(tmps.id) as numero_facturas_det, sum(pagos.monto) as monto_total_det, pagos.id_pago')
				->join('pagos', 'tmps.id','=',  'pagos.id_inscripcion')
				->whereNull('tmps.deleted_at')
				->where('tmps.pagado', '=', 1)
				// ->where('tmps.facturado', '=', 1)
				->where('tmps.id_cierre_caja', '=', 0)
				->where('pagos.estado', '=','A')
				->whereDate('tmps.created_at', '=', $request->fecha)
				->where('tmps.created_by_id', '=',auth()->user()->id)
				->groupBy('pagos.id_pago')
				->get();


		$existeCaja = CierreCaja::selectRaw('max(secuencia) as secuencia')->where('id_punto_servicio', auth()->user()->id)->whereDate('fecha', $request->fecha)->first();

		if(is_null($existeCaja->secuencia)) {
			$secuencia = 1;
		} else {
			$secuencia = $existeCaja->secuencia + 1;
		}
		
		try {
			DB::beginTransaction();
			
			$caja = new CierreCaja();

			$caja->fecha = $request->fecha;
			$caja->secuencia = $secuencia;
			$caja->id_punto_servicio = auth()->user()->id;
			$caja->monto_total = $facturas->monto_total;
			$caja->numero_facturas = $facturas->numero_facturas;
			$caja->created_by_id = auth()->user()->id;
			$caja->save();
			

 			foreach($detalle_facturas as $data){
				$detalleCaja = new CierreCajaDetalle();
				$detalleCaja->id_cierre_caja = $caja->id;
				$detalleCaja->id_forma_pago = $data->id_pago;
				$detalleCaja->numero_facturas = $data->numero_facturas_det;
				$detalleCaja->monto = $data->monto_total_det;
				$detalleCaja->created_by_id = $caja->created_by_id;
				$detalleCaja->save();
			}

			foreach($tmps as $t) {
				$registro = Inscripcion::find($t->id);
				$registro->id_cierre_caja = $caja->id;
				$registro->save();
			}


			DB::commit();

			
        } catch (\Exception $e) {
            DB::rollBack();
            dd ('Error en el sistema', $e);
        }

		$cierrecaja = CierreCaja::select('cierre_caja.*', 'users.name')->join('users', 'cierre_caja.id_punto_servicio', 'users.id')->get();
		return view('admin.cierreCaja.index', compact('cierrecaja'));
	}
	
	public function show($id)
    {
		abort_if(Gate::denies('cierre_caja_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');	
		$caja = DB::table('cierre_caja')->where('id', $id)->first();
		$detalle = DB::table('cierre_caja_detalle')->where('id_cierre_caja',$id)->get();
		$facturas = Inscripcion::where('id_cierre_caja',$id)->get();
		$puntoventa = User::find($caja->id_punto_servicio);

		return view('admin.cierreCaja.show', compact('caja', 'detalle','facturas', 'puntoventa'));
    }
	
	
}