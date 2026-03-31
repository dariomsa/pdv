<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Inscripcion;
use App\Models\Participante;
use App\Models\Facturacion;
use App\Models\FacturacionDetalle;
use App\Models\Pago;
use App\Models\PagoDetalle;
use App\Models\InventarioTotal;
use App\Models\InscripcionTipo;
use Illuminate\Support\Facades\Log;

use App\helpers\ParticipanteHelper;

class InscripcionNormalApiController extends Controller
{
    public function inscripciones_insert(Request $request)
    {
		
		Log::info('json: ' . json_encode($request->all()));
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'forma_pago_id' => 'required|exists:formas_pago,id',
            'referencia' => 'required|string|max:255',
            'factura' => 'required|array',
            'factura.tipo_documento' => 'required|string|in:C,R,E',
            'factura.numero_documento' => 'required|string',
            'factura.nombre' => 'required|string',
            'factura.telefono' => 'required|string',
            'factura.direccion' => 'required|string',
            'factura.email' => 'required|email',
            'participantes' => 'required|array|min:1',
            'participantes.*.nombres' => 'required|string',
            'participantes.*.apellidos' => 'required|string',
            'participantes.*.fecha_nacimiento' => 'required|date',
            'participantes.*.talla' => 'required|string',
            'participantes.*.email' => 'required|email',
       
        ]);

        // cuenta los participantes
        $facturaTipo = count($request->participantes) > 1 ? 'Mult' : 'Ind';


        try {
            DB::beginTransaction();

            // Crear inscripción
            $inscripcion = Inscripcion::create([
                'created_by_id' => $request->user_id,
                'estado' => 1,
            ]);

                 

            $inscripcionId =$inscripcion->id;

       

                // Crear factura
            $factura =Facturacion::create ([
                'inscripcion_id'         => $inscripcionId ,
                'fact_tipo_documento'    => $request->factura['tipo_documento'],
                'numero_doc_facturacion' => $request->factura['numero_documento'],
                'nombre_facturacion'     => $request->factura['nombre'],
                'apellido_facturacion'   => $request->factura['nombre'],
                'email_facturacion'      => $request->factura['email'],
                'telefono_facturacion'   => $request->factura['telefono'],
                'direccion_facturacion'  => $request->factura['direccion'],
                'nota_facturacion'       => $request->factura['nota_facturacion'],
                'valor'                  => $request->factura['valor'],
                'iva'                    => $request->factura['iva'],
                'pagado'                 => 1
            ]);

              


           

              // Crear pago principal

            $pagoPrincipal = Pago::create ([
            'inscripcion_id'  => $inscripcionId,
            'facturacion_id'  => $factura->id,
            'pago_id'         => $request->forma_pago_id,
            'total'           => $request->factura['valor'],
            'referencia'      => $request->referencia,
            'estado'          => 'PAGADO' // o 'PENDIENTE' si no está confirmado
            ]);


            

             // Actualizar estado de la inscripción
            Inscripcion::where('id', $inscripcionId)->update(['estado' => 1]);



            foreach ($request->participantes as $p) {
                // Obtener valores desde tipo inscripción
              

                // Crear participante
            $participante = Participante::create([
            'inscripcion_id' => $inscripcionId,
            'created_by_id' => $request->user_id,
            'tipo_inscripcion' => $p['tipo_inscripcion'],
            'tipo_documento' => $p['tipo_documento'],
            'numero_documento' => $p['numero_documento'],
            'nombres' => $p['nombres'],
            'apellidos' => $p['apellidos'],
            'nacionalidad' => $p['nacionalidad'],
            'genero' => $p['genero'],
            'fecha_nacimiento' => $p['fecha_nacimiento'],
            'categoria' => $p['categoria'],
            'talla' => $p['talla'],
            'celular' => $p['celular'],
            'email' => $p['email'],

            'direccion' => $p['direccion'],
            'provincia' => $p['provincia'],
            'ciudad' => $p['ciudad'],
            'parroquia' => $p['parroquia'],
            'corral' => $p['corral'],
            'tercera_edad' => $p['tercera_edad'],
            'discapacidad' => $p['discapacidad'],
            'emergencia_nombre' => $p['emergencia_nombre'],
            'emergencia_celular' => $p['emergencia_celular'],
             'factura'=> $facturaTipo
            
         
            
   
]);

        
$tipo = InscripcionTipo::find($p['tipo_inscripcion']);
$valorBase = $tipo ? $tipo->valor : 0;

// Factor de descuento (solo si NO es tercera edad)
$factor = ($request->factura['nota_facturacion'] === 'P10KM2025' && $p['tercera_edad'] != 1) 
    ? 0.8333333 
    : 1.0;

// Calcular valor final
$valor = ($p['tercera_edad'] == 1)
    ? $valorBase * 0.5   // tercera edad = 50%
    : $valorBase * $factor; // normal = aplica descuento si corresponde
/////

Log::info('Nota: ' . $request->factura['nota_facturacion']);
Log::info('Valor: ' . $valor);
Log::info('factor: ' . $factor);


               

                $detalle=FacturacionDetalle::create([
                    'facturacion_id' => $factura->id,
                    'participante_id' => $participante->id,
                    'valor' => $valor,
                    'pagado' => 0
                    
                ]);

             

                $pago=PagoDetalle::create([
                'participante_id' =>  $participante->id,
                'forma_pago_id' => $request->forma_pago_id, // debe ser ID de formas_pago
                'pagos_id'   => $pagoPrincipal->id,   // Relación con pago_principal
                'referencia'      => $request->referencia ?? null,
                'monto'           => $valor,
                'estado'          => 'A',
                'created_by_id' => $request->user_id,
            ]);

            //descontar stock

           // Obtener carrera_id desde tabla inscripcion_tipos
$tipo = \App\Models\InscripcionTipo::find($participante->tipo_inscripcion);

if (!$tipo) {
    throw new \Exception("Tipo inscripción inválido: {$participante->tipo_inscripcion}");
}

$carreraId = $tipo->carrera_id;

// Descontar stock correctamente
DB::table('inventario_total')
    ->where('talla', $participante->talla)
    ->where('genero', $participante->genero)
    ->where('carrera_id', $carreraId)
    ->decrement('stock_restante', 1);


            }

   

         

            DB::commit();

            return response()->json([
                'status' => 'ok',
                'mensaje' => 'Inscripción creada correctamente',
                'inscripcion_id' => $inscripcion->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en Inscripcion API: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'mensaje' => 'Error interno. Verifique los datos.'
            ], 500);
        }
    }

///verificar inscritos

    public function verificar(Request $request)
    {
        $request->validate([
            'numero_documento' => 'required|string',
        ]);

        try {
            $numero = $request->numero_documento;

            // usar el helper para consultar
            $existe = ParticipanteHelper::yaInscrito($numero);

            return response()->json([
                'inscrito' => $existe,
                'mensaje' => $existe ? 'El participante ya está inscrito' : 'No está inscrito aún',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al verificar inscripción: ' . $e->getMessage());

            return response()->json([
                'error' => 'Error al procesar la solicitud'
            ], 500);
        }
    }

}


