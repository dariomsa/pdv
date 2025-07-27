<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Facturacion;
use App\Models\FacturacionCorporativa;
use App\Http\Controllers\Controller;

class FacturacionController extends Controller
{
    // Ruta: GET /facturar


public function index()
{
    $facturas = Facturacion::with('inscripcion')
        ->where('pagado', 1)
        ->where('enviado_facturacion', 0)
        ->whereHas('inscripcion', function ($q) {
            $q->where('estado', 1);
        })
        ->limit(5)->get();

      

    if ($facturas->isEmpty()) {
       dd('No hay facturas pendientes de envío.');
    }

    foreach ($facturas as $facturacion) {
      

         switch ($facturacion->fact_tipo_documento) {
        case 'C':
            $tipo_identificacion = '05';
            break;
        case 'R':
            $tipo_identificacion = '04';
            break;
        case 'E':
            $tipo_identificacion = '08';
            break;
        default:
            $tipo_identificacion = '05';
            break;
    }

        $precio_unitario = round($facturacion->valor / 1.15, 2);

        $payload = [
            "api_key" => "API_10782_11774_65b16aea1b6a2",
            "codigoDoc" => "01",
            "emisor" => [
                "manejo_interno_secuencia" => "SI",
                "fecha_emision" => now()->format('Y/m/d')
            ],
            "comprador" => [
                "tipo_identificacion" => $tipo_identificacion,
                "identificacion" => $facturacion->numero_doc_facturacion,
                "razon_social" => $facturacion->nombre_facturacion,
                "direccion" => $facturacion->direccion_facturacion,
                "telefono" => $facturacion->telefono_facturacion,
                "celular" => null,
                "correo" => $facturacion->email_facturacion,
            ],
            "items" => [[
                "codigo_principal" => "003",
                "codigo_auxiliar" => null,
                "descripcion" => "Inscripciones Quito 15Krace 2025",
                "tipoproducto" => 2,
                "tipo_iva" => 4,
                "precio_unitario" => $precio_unitario,
                "cantidad" => 1,
                "descuento" => 0,
            ]],
            "informacion_adicional" => [[
                "nombre" => "Observaciones",
                "detalle" => "id.{$facturacion->inscripcion_id}"
            ]]
        ];

        try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://facturaec.grupoavec.com/plataforma/api/v2/factura/emision', $payload);

            Log::info("Factura #{$facturacion->id} enviada: " . $response->body());

            $json = $response->json();

            if (isset($json['claveacceso'])) {
                $facturacion->update([
                    'enviado_facturacion' => 1,
                    'clave_acceso' => $json['claveacceso'],
                ]);
            } else {
                $facturacion->update([
                    'enviado_facturacion' => 1,
                ]);
                
                Log::warning("Factura #{$facturacion->id} no tiene clave de acceso en respuesta.");
            }

        } catch (\Exception $e) {
            Log::error("Error al enviar factura #{$facturacion->id}: " . $e->getMessage());
        }
    }

    dd('end');
}


    // Ruta: GET /facturar/{corp_id}
   
public function show($id)
{
    $facturacion = FacturacionCorporativa::where('enviado_facturacion', 0)

        ->where('pagado', 1)
        ->where('id', $id)
        ->whereHas('inscripcion', function ($q) {
            $q->where('estado', 1);
        })
        ->first();


    if (!$facturacion) {
            dd('No hay facturas corpporativas pendientes de envío.');
    }

    // Fecha actual formateada
    $fechaEmision = now()->format('Y/m/d');

    // Tipo de identificación
       switch ($facturacion->tipo_documento) {
        case 'C':
            $tipo_identificacion = '05';
            break;
        case 'R':
            $tipo_identificacion = '04';
            break;
        case 'E':
            $tipo_identificacion = '08';
            break;
        default:
            $tipo_identificacion = '05';
            break;
    }

    // Calcular valor sin IVA
    $precio_unitario = round($facturacion->valor / 1.15, 2);

    // Payload de la factura
    $payload = [
        "api_key" => "API_10782_11774_65b16aea1b6a2",
        "codigoDoc" => "01",
        "emisor" => [
            "manejo_interno_secuencia" => "SI",
            "fecha_emision" => $fechaEmision
        ],
        "comprador" => [
            "tipo_identificacion" => $tipo_identificacion,
            "identificacion" => $facturacion->numero_documento,
            "razon_social" => $facturacion->razon_social,
            "direccion" => $facturacion->direccion,
            "telefono" => $facturacion->telefono,
            "celular" => null,
            "correo" => $facturacion->email,
        ],
        "items" => [[
            "codigo_principal" => "003",
            "codigo_auxiliar" => null,
            "descripcion" => "Inscripciones Corporativas Carrera Quito15K Adidas 2025",
            "tipoproducto" => 2,
            "tipo_iva" => 4,
            "precio_unitario" => $precio_unitario,
            "cantidad" => 1,
            "descuento" => 0
        ]],
        "informacion_adicional" => [[
            "nombre" => "Observaciones",
            "detalle" => "CORPORATIVAS"
        ]]
    ];

       //  dd( $payload);

    // Enviar a la API con HTTP de Laravel
   

     try {
            $response = Http::timeout(15)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://facturaec.grupoavec.com/plataforma/api/v2/factura/emision', $payload);

            Log::info("Factura - Corporativa #{$facturacion->id} enviada: " . $response->body());

            $json = $response->json();

            if (isset($json['claveacceso'])) {
                $facturacion->update([
                    'enviado_facturacion' => 1,
                    'clave_acceso' => $json['claveacceso'],
                ]);
            } else {
                $facturacion->update([
                    'enviado_facturacion' => 1,
                ]);
                Log::warning("Factura #{$facturacion->id} no tiene clave de acceso en respuesta.");
            }

        } catch (\Exception $e) {
            Log::error("Error al enviar factura #{$facturacion->id}: " . $e->getMessage());
        }

            dd('end corporativas');


}




}

