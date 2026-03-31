<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ajuste;
use App\Models\Facturacion;
use App\Models\FacturacionDetalle;
use App\Models\FormaPago;
use App\Models\InscripcionTipo;
use App\Models\PagoDetalle;
use App\Models\Participante;
use App\Models\ParticipanteCorporativa;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AjusteController extends Controller
{
     public function index(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        $ajustes = Ajuste::query()
            ->leftJoin('formas_pago', 'formas_pago.id', '=', 'ajuste.metodo_pago')
            ->select('ajuste.*', 'formas_pago.metodo_pago as metodo_pago_nombre')
            ->when($fecha_desde, function ($query) use ($fecha_desde) {
                $query->whereDate('ajuste.updated_at', '>=', $fecha_desde);
            })
            ->when($fecha_hasta, function ($query) use ($fecha_hasta) {
                $query->whereDate('ajuste.updated_at', '<=', $fecha_hasta);
            })
            ->orderByDesc('ajuste.updated_at')
            ->orderByDesc('ajuste.id')
            ->get();

        $formasPago = FormaPago::query()
            ->whereRaw("UPPER(REPLACE(metodo_pago, ' ', '')) IN ('EFECTIVO', 'DEUNA','TRANSFERENCIA')")
            ->orderBy('metodo_pago')
            ->get();

        return view('ajuste.index', compact('ajustes', 'fecha_desde', 'fecha_hasta', 'formasPago'));
    }

    public function reporte(Request $request)
    {
        return $this->index($request);
    }

    public function buscarDocumento(Request $request)
    {
        $request->validate([
            'numero_documento' => 'required|string',
        ]);

        $numeroDocumento = trim($request->numero_documento);

        $participante = Participante::with('tipoInscripcion')
            ->where('numero_documento', $numeroDocumento)
            ->first();

        if ($participante) {
            $yaExisteAjuste = Ajuste::where('numero_documento', $participante->numero_documento)->exists();
            $puedeActualizar = (int) $participante->tipo_inscripcion === 1
                && $participante->created_at
                && $participante->created_at->gt(\Carbon\Carbon::parse('2026-03-01 00:00:00'))
                && !$yaExisteAjuste;
            $tipoNombre = optional($participante->tipoInscripcion)->nombre ?? 'Sin inscripción';
            $preview = $puedeActualizar ? $this->buildAjusteData($participante) : null;

            if ($yaExisteAjuste) {
                $message = 'Encontrado en participantes. Corresponde a ' . $tipoNombre . ' y ya existe un registro de ajuste para este documento.';
            } elseif ($puedeActualizar) {
                $message = 'Encontrado en participantes. Corresponde a ' . $tipoNombre . ' y se puede actualizar.';
            } else {
                $message = 'Encontrado en participantes. Corresponde a ' . $tipoNombre . ' y no se puede actualizar. Solo aplica para registros creados después del 1 de marzo de 2026.';
            }

            return response()->json([
                'found' => true,
                'source' => 'participantes',
                'can_update' => $puedeActualizar,
                'message' => $message,
                'ya_existe_ajuste' => $yaExisteAjuste,
                'data' => [
                    'id' => $participante->id,
                    'numero_documento' => $participante->numero_documento,
                    'nombre' => $participante->nombres,
                    'apellido' => $participante->apellidos,
                    'genero' => $participante->genero,
                    'tipo_inscripcion' => $participante->tipo_inscripcion,
                ],
                'preview' => $preview,
            ]);
        }

        $corporativo = ParticipanteCorporativa::with('tipoInscripcion')
            ->where('numero_documento', $numeroDocumento)
            ->first();

        if ($corporativo) {
            $tipoNombre = optional($corporativo->tipoInscripcion)->nombre ?? 'Sin inscripción';

            return response()->json([
                'found' => true,
                'source' => 'participantes_corporativas',
                'can_update' => false,
                'message' => 'Encontrado en corporativas. Corresponde a ' . $tipoNombre . ' y no se puede actualizar.',
                'data' => [
                    'id' => $corporativo->id,
                    'numero_documento' => $corporativo->numero_documento,
                    'nombre' => $corporativo->nombres,
                    'apellido' => $corporativo->apellidos,
                    'genero' => $corporativo->genero,
                    'tipo_inscripcion' => $corporativo->tipo_inscripcion,
                ],
            ]);
        }

        return response()->json([
            'found' => false,
            'message' => 'No se encontró el número de documento en participantes ni en corporativas.',
        ], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'participante_id' => 'required|integer|exists:participantes,id',
            'metodo_pago' => 'required|integer|exists:formas_pago,id',
            'referencia' => 'nullable|string|max:255',
        ]);

        $participante = Participante::with('tipoInscripcion')->findOrFail($request->participante_id);

        if ((int) $participante->tipo_inscripcion !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede generar ajuste para participantes con tipo de inscripción 1.',
            ], 422);
        }

        if (Ajuste::where('numero_documento', $participante->numero_documento)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este documento ya tiene un ajuste registrado.',
            ], 422);
        }

        $data = $this->buildAjusteData($participante, [
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
        ]);

        DB::beginTransaction();

        try {
            $ajuste = Ajuste::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajuste generado correctamente.',
                'ajuste_id' => $ajuste->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el ajuste.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function buildAjusteData(Participante $participante, array $overrides = []): array
    {
        $factura = Facturacion::where('inscripcion_id', $participante->inscripcion_id)->first();
        $facturaDetalle = FacturacionDetalle::where('participante_id', $participante->id)->first();
        $pagoDetalle = PagoDetalle::with('formaPago')
            ->where('participante_id', $participante->id)
            ->first();

        $tipoDestino = InscripcionTipo::find(5);
        $valorPagado = (float) optional($facturaDetalle)->valor;
        $valorDestino = (float) optional($tipoDestino)->valor;
        $ivaRate = (float) optional($tipoDestino)->iva;
        $diferenciaTotal = round($valorDestino - $valorPagado, 2);

        if ($ivaRate > 0) {
            $subTotal = round($diferenciaTotal / (1 + $ivaRate), 2);
            $iva = round($diferenciaTotal - $subTotal, 2);
        } else {
            $subTotal = $diferenciaTotal;
            $iva = 0;
        }

        return [
            'id_participante' => $participante->id,
            'fecha' => optional($participante->created_at)->toDateString(),
            'numero_factura' => null,
            'nombre_facturacion' => optional($factura)->nombre_facturacion,
            'numero_documento_facturacion' => optional($factura)->numero_doc_facturacion,
            'correo_facturacion' => optional($factura)->email_facturacion,
            'telefono_facturacion' => optional($factura)->telefono_facturacion,
            'numero_documento' => $participante->numero_documento,
            'nombre' => $participante->nombres,
            'apellido' => $participante->apellidos,
            'genero' => $participante->genero,
            'corral' => $participante->corral,
            'talla_camiseta' => $participante->talla,
            'correo' => $participante->email,
            'metodo_pago' => $overrides['metodo_pago'] ?? null,
            'referencia' => $overrides['referencia'] ?? null,
            'sub_total' => $subTotal,
            'iva' => $iva,
            'total' => $diferenciaTotal,
            'valor_pagado' => $valorPagado,
        ];
    }
}
