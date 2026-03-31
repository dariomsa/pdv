@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        <strong>Ajuste Corporativas</strong>
    </div>

    <div class="card-body">
        <form method="get">
            <div class="row">
                <div class="col-12 col-md-2">
                    <label><small>FECHA DESDE</small></label>
                    <input type="date" name="fecha_desde" class="form-control" value="{{ $fecha_desde }}">
                </div>

                <div class="col-12 col-md-2">
                    <label><small>FECHA HASTA</small></label>
                    <input type="date" name="fecha_hasta" class="form-control" value="{{ $fecha_hasta }}">
                </div>

                <div class="col-4 col-md-2">
                    <label class="control-label">&nbsp;</label><br>
                    <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                </div>

                <div class="col-4 col-md-2">
                    <label class="control-label">&nbsp;</label><br>
                    <button class="btn btn-success w-100" type="button" data-toggle="modal" data-target="#buscarDocumentoModalCorporativas">
                        Buscar
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover datatable datatable-AjusteCorporativas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Inscripción</th>
                            <th>Fecha Actualización</th>
                            <th>Número de Factura</th>
                            <th>Nombre de Facturación</th>
                            <th>Número Documento</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Género</th>
                            <th>Corral</th>
                            <th>Talla Camiseta</th>
                            <th>Correo</th>
                            <th>Método de Pago</th>
                            <th>Referencia</th>
                            <th>Sub Total</th>
                            <th>IVA</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ajustes as $ajuste)
                            <tr>
                                <td>{{ $ajuste->id }}</td>
                                <td>{{ $ajuste->fecha }}</td>
                                <td>{{ $ajuste->updated_at }}</td>
                                <td>{{ $ajuste->numero_factura ? substr($ajuste->numero_factura, 24, 15) : 'ND' }}</td>
                                <td>{{ $ajuste->nombre_facturacion }}</td>
                                <td>{{ $ajuste->numero_documento }}</td>
                                <td>{{ $ajuste->nombre }}</td>
                                <td>{{ $ajuste->apellido }}</td>
                                <td>{{ $ajuste->genero }}</td>
                                <td>{{ $ajuste->corral }}</td>
                                <td>{{ $ajuste->talla_camiseta }}</td>
                                <td>{{ $ajuste->correo }}</td>
                                <td>{{ $ajuste->metodo_pago_nombre ?? $ajuste->metodo_pago }}</td>
                                <td>{{ $ajuste->referencia }}</td>
                                <td>{{ number_format((float) $ajuste->sub_total, 2) }}</td>
                                <td>{{ number_format((float) $ajuste->iva, 2) }}</td>
                                <td>{{ number_format((float) $ajuste->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="buscarDocumentoModalCorporativas" tabindex="-1" role="dialog" aria-labelledby="buscarDocumentoModalCorporativasLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarDocumentoModalCorporativasLabel">Buscar por Número de Documento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="buscar_numero_documento_corp">Número de Documento</label>
                    <input type="text" id="buscar_numero_documento_corp" class="form-control" placeholder="Ingrese el número de documento">
                </div>

                <div id="resultado_busqueda_documento_corp" style="display:none;">
                    <div class="alert mb-3" id="resultado_busqueda_alerta_corp"></div>
                    <input type="hidden" id="resultado_participante_id_corp">

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Fecha</label>
                                <input type="text" id="resultado_fecha_corp" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Nombre de Facturación</label>
                                <input type="text" id="resultado_nombre_facturacion_corp" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Nombre Participante</label>
                                <input type="text" id="resultado_nombre_corp" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Apellido Participante</label>
                                <input type="text" id="resultado_apellido_corp" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div id="bloque_campos_actualizables_corp" style="display:none;">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Género Participante</label>
                                    <input type="text" id="resultado_genero_corp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Corral Participante</label>
                                    <input type="text" id="resultado_corral_corp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Talla Camiseta Participante</label>
                                    <input type="text" id="resultado_talla_corp" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Correo Participante</label>
                                    <input type="text" id="resultado_correo_corp" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Valor Pagado</label>
                                    <input type="text" id="valor_pagado_corp" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Diferencia Ajuste</label>
                                    <input type="text" id="diferencia_ajuste_corp" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Método de Pago</label>
                                    <select id="metodo_pago_corp" class="form-control">
                                        <option value="">-- Seleccione --</option>
                                        @foreach($formasPago as $formaPago)
                                            <option value="{{ $formaPago->id }}">{{ $formaPago->metodo_pago }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Referencia</label>
                                    <input type="text" id="referencia_corp" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnBuscarDocumentoCorp">Buscar</button>
                <button type="button" class="btn btn-success" id="btnGuardarAjusteCorp" style="display:none;">Guardar Ajuste</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
<script>
    $(function () {
        let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

        $.extend(true, $.fn.dataTable.defaults, {
            order: [[0, 'desc']],
            pageLength: 100,
        });

        let table = $('.datatable-AjusteCorporativas:not(.ajaxTable)').DataTable({ buttons: dtButtons });

        $('#btnBuscarDocumentoCorp').on('click', function () {
            let documento = $('#buscar_numero_documento_corp').val().trim();

            if (!documento) {
                return;
            }

            $.ajax({
                url: '{{ route('admin.ajuste_corporativas.buscar_documento') }}',
                method: 'GET',
                data: { numero_documento: documento },
            }).done(function (response) {
                let alerta = $('#resultado_busqueda_alerta_corp');

                $('#resultado_busqueda_documento_corp').show();
                $('#resultado_participante_id_corp').val(response.data.id || '');
                $('#resultado_fecha_corp').val((response.preview && response.preview.fecha) || '');
                $('#resultado_nombre_facturacion_corp').val((response.preview && response.preview.nombre_facturacion) || '');
                $('#resultado_nombre_corp').val(response.data.nombre || '');
                $('#resultado_apellido_corp').val(response.data.apellido || '');
                $('#resultado_genero_corp').val(response.data.genero || '');

                alerta.removeClass('alert-danger alert-warning alert-success')
                    .addClass(response.can_update ? 'alert-success' : 'alert-warning')
                    .text(response.message);

                if (response.can_update) {
                    $('#bloque_campos_actualizables_corp').show();
                    $('#resultado_corral_corp').val((response.preview && response.preview.corral) || '');
                    $('#resultado_talla_corp').val((response.preview && response.preview.talla_camiseta) || '');
                    $('#resultado_correo_corp').val((response.preview && response.preview.correo) || '');
                    $('#valor_pagado_corp').val((response.preview && response.preview.valor_pagado !== undefined) ? parseFloat(response.preview.valor_pagado).toFixed(2) : '0.00');
                    $('#diferencia_ajuste_corp').val((response.preview && response.preview.total !== undefined) ? parseFloat(response.preview.total).toFixed(2) : '0.00');
                    $('#metodo_pago_corp').val('');
                    $('#referencia_corp').val('');
                    $('#btnGuardarAjusteCorp').show();
                } else {
                    $('#bloque_campos_actualizables_corp').hide();
                    $('#resultado_corral_corp, #resultado_talla_corp, #resultado_correo_corp, #valor_pagado_corp, #diferencia_ajuste_corp, #metodo_pago_corp, #referencia_corp').val('');
                    $('#btnGuardarAjusteCorp').hide();
                }

                table.column(5).search(documento).draw();
                $('#btnBuscarDocumentoCorp').hide();
            }).fail(function (xhr) {
                let mensaje = 'No se encontró información.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }

                $('#resultado_busqueda_documento_corp').show();
                $('#resultado_participante_id_corp').val('');
                $('#resultado_fecha_corp, #resultado_nombre_facturacion_corp, #resultado_nombre_corp, #resultado_apellido_corp, #resultado_genero_corp').val('');
                $('#bloque_campos_actualizables_corp').hide();
                $('#resultado_corral_corp, #resultado_talla_corp, #resultado_correo_corp, #valor_pagado_corp, #diferencia_ajuste_corp, #metodo_pago_corp, #referencia_corp').val('');
                $('#btnGuardarAjusteCorp').hide();
                $('#btnBuscarDocumentoCorp').show();
                $('#resultado_busqueda_alerta_corp')
                    .removeClass('alert-success alert-warning')
                    .addClass('alert-danger')
                    .text(mensaje);
            });
        });

        $('#btnGuardarAjusteCorp').on('click', function () {
            let participanteId = $('#resultado_participante_id_corp').val();
            let metodoPago = $('#metodo_pago_corp').val();

            if (!participanteId) {
                return;
            }

            if (!metodoPago) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Método de pago requerido',
                    text: 'Debe seleccionar un método de pago.',
                    confirmButtonText: 'Aceptar'
                });
                return;
            }

            $.ajax({
                url: '{{ route('admin.ajuste_corporativas.store') }}',
                method: 'POST',
                data: {
                    participante_id: participanteId,
                    metodo_pago: metodoPago,
                    referencia: $('#referencia_corp').val().trim(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
            }).done(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Ajuste corporativo generado',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                }).then(function () {
                    $('#buscarDocumentoModalCorporativas').modal('hide');
                    window.location.reload();
                });
            }).fail(function (xhr) {
                let mensaje = 'No se pudo guardar el ajuste corporativo.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje,
                    confirmButtonText: 'Aceptar'
                });
            });
        });

        $('#buscarDocumentoModalCorporativas').on('hidden.bs.modal', function () {
            $('#buscar_numero_documento_corp').val('');
            $('#resultado_busqueda_documento_corp').hide();
            $('#resultado_participante_id_corp').val('');
            $('#resultado_fecha_corp, #resultado_nombre_facturacion_corp, #resultado_nombre_corp, #resultado_apellido_corp, #resultado_genero_corp, #resultado_corral_corp, #resultado_talla_corp, #resultado_correo_corp, #valor_pagado_corp, #diferencia_ajuste_corp, #metodo_pago_corp, #referencia_corp').val('');
            $('#bloque_campos_actualizables_corp').hide();
            $('#btnGuardarAjusteCorp').hide();
            $('#btnBuscarDocumentoCorp').show();
            $('#resultado_busqueda_alerta_corp').removeClass('alert-danger alert-warning alert-success').text('');
            table.search('').columns().search('').draw();
        });

        $('#buscar_numero_documento_corp').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btnBuscarDocumentoCorp').trigger('click');
            }
        });
    });
</script>
@endsection
