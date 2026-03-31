<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AjusteCorporativa;
use App\Models\FormaPago;
use App\Models\InscripcionTipo;
use App\Models\ParticipanteCorporativa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjusteCorporativasController extends Controller
{
    public function index(Request $request)
    {
        $fecha_desde = $request->input('fecha_desde');
        $fecha_hasta = $request->input('fecha_hasta');

        $ajustes = AjusteCorporativa::query()
            ->leftJoin('formas_pago', 'formas_pago.id', '=', 'ajuste_corporatuvas.metodo_pago')
            ->select('ajuste_corporatuvas.*', 'formas_pago.metodo_pago as metodo_pago_nombre')
            ->when($fecha_desde, function ($query) use ($fecha_desde) {
                $query->whereDate('ajuste_corporatuvas.updated_at', '>=', $fecha_desde);
            })
            ->when($fecha_hasta, function ($query) use ($fecha_hasta) {
                $query->whereDate('ajuste_corporatuvas.updated_at', '<=', $fecha_hasta);
            })
            ->orderByDesc('ajuste_corporatuvas.updated_at')
            ->orderByDesc('ajuste_corporatuvas.id')
            ->get();

        $formasPago = FormaPago::query()
            ->whereRaw("UPPER(REPLACE(metodo_pago, ' ', '')) IN ('EFECTIVO', 'DEUNA')")
            ->orderBy('metodo_pago')
            ->get();

        return view('ajuste_corporativas.index', compact('ajustes', 'fecha_desde', 'fecha_hasta', 'formasPago'));
    }

    public function buscarDocumento(Request $request)
    {
        $request->validate([
            'numero_documento' => 'required|string',
        ]);

        $numeroDocumento = trim($request->numero_documento);

        $participante = ParticipanteCorporativa::with('tipoInscripcion')
            ->where('numero_documento', $numeroDocumento)
            ->first();

        if (!$participante) {
            return response()->json([
                'found' => false,
                'message' => 'No se encontró el número de documento en participantes corporativas.',
            ], 404);
        }

        $yaExisteAjuste = AjusteCorporativa::where('numero_documento', $participante->numero_documento)->exists();
        $puedeActualizar = $participante->created_at
            && $participante->created_at->gt(Carbon::parse('2026-03-01 00:00:00'))
            && !$yaExisteAjuste;

        $tipoNombre = optional($participante->tipoInscripcion)->nombre ?? 'Sin inscripción';
        $preview = $puedeActualizar ? $this->buildAjusteData($participante) : null;

        if ($yaExisteAjuste) {
            $message = 'Encontrado en participantes corporativas. Corresponde a ' . $tipoNombre . ' y ya existe un registro de ajuste para este documento.';
        } elseif ($puedeActualizar) {
            $message = 'Encontrado en participantes corporativas. Corresponde a ' . $tipoNombre . ' y se puede actualizar.';
        } else {
            $message = 'Encontrado en participantes corporativas. Corresponde a ' . $tipoNombre . ' y no se puede actualizar. Solo aplica para registros creados después del 1 de marzo de 2026.';
        }

        return response()->json([
            'found' => true,
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

    public function store(Request $request)
    {
        $request->validate([
            'participante_id' => 'required|integer|exists:participantes_corporativas,id',
            'metodo_pago' => 'required|integer|exists:formas_pago,id',
            'referencia' => 'nullable|string|max:255',
        ]);

        $participante = ParticipanteCorporativa::with('tipoInscripcion')->findOrFail($request->participante_id);

        if (AjusteCorporativa::where('numero_documento', $participante->numero_documento)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Este documento ya tiene un ajuste corporativo registrado.',
            ], 422);
        }

        $data = $this->buildAjusteData($participante, [
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
        ]);

        DB::beginTransaction();

        try {
            $ajuste = AjusteCorporativa::create($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ajuste corporativo generado correctamente.',
                'ajuste_id' => $ajuste->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'No se pudo generar el ajuste corporativo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function buildAjusteData(ParticipanteCorporativa $participante, array $overrides = []): array
    {
        $tipoDestino = InscripcionTipo::find(5);
        $valorPagado = (float) ($participante->valor ?? 0);
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
            'tipo_documento' => 'C',
            'nombre_facturacion' => trim(($participante->nombres ?? '') . ' ' . ($participante->apellidos ?? '')),
            'numero_documento_facturacion' => $participante->numero_documento,
            'direccion_facturacion' => 'S/N',
            'correo_facturacion' => $participante->email,
            'telefono_facturacion' => $participante->celular,
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
            'enviado_facturacion' => 0,
            'enviado_front' => 0,
        ];
    }
}
