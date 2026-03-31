@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        <strong>Ajuste</strong>
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
                    <button class="btn btn-success w-100" type="button" data-toggle="modal" data-target="#buscarDocumentoModal">
                        Buscar
                    </button>
                </div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-hover datatable datatable-Ajuste">
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
                                <td>{{ $ajuste->numero_factura ? substr($ajuste->numero_factura, 24, 15) : '' }}</td>

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

<div class="modal fade" id="buscarDocumentoModal" tabindex="-1" role="dialog" aria-labelledby="buscarDocumentoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="buscarDocumentoModalLabel">Buscar por Número de Documento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="buscar_numero_documento">Número de Documento</label>
                    <input type="text" id="buscar_numero_documento" class="form-control" placeholder="Ingrese el número de documento">
                </div>

                <div id="resultado_busqueda_documento" style="display:none;">
                    <div class="alert mb-3" id="resultado_busqueda_alerta"></div>
                    <input type="hidden" id="resultado_participante_id">
                    <input type="hidden" id="hidden_numero_documento">
                    <input type="hidden" id="hidden_numero_factura">
                    <input type="hidden" id="hidden_numero_documento_facturacion">
                    <input type="hidden" id="hidden_correo_facturacion">
                    <input type="hidden" id="hidden_telefono_facturacion">

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Fecha</label>
                                <input type="text" id="resultado_fecha" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Nombre de Facturación</label>
                                <input type="text" id="resultado_nombre_facturacion" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Nombre Participante</label>
                                <input type="text" id="resultado_nombre" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label>Apellido Participante</label>
                                <input type="text" id="resultado_apellido" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div id="bloque_campos_actualizables" style="display:none;">
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Género Participante</label>
                                    <input type="text" id="resultado_genero" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Corral Participante</label>
                                    <input type="text" id="resultado_corral" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Talla Camiseta Participante</label>
                                    <input type="text" id="resultado_talla" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-4">
                                <div class="form-group">
                                    <label>Correo Participante</label>
                                    <input type="text" id="resultado_correo" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Valor Pagado</label>
                                    <input type="text" id="campo_1" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Diferencia Ajuste</label>
                                    <input type="text" id="campo_2" class="form-control" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label>Método de Pago</label>
                                    <select id="metodo_pago" class="form-control">
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
                                    <input type="text" id="referencia" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btnBuscarDocumento">Buscar</button>
                <button type="button" class="btn btn-success" id="btnGuardarAjuste" style="display:none;">Guardar Ajuste</button>
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

        let table = $('.datatable-Ajuste:not(.ajaxTable)').DataTable({ buttons: dtButtons });

        $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });

        $('#btnBuscarDocumento').on('click', function () {
            let documento = $('#buscar_numero_documento').val().trim();

            if (!documento) {
                return;
            }

            $.ajax({
                url: '{{ route('admin.ajuste.buscar_documento') }}',
                method: 'GET',
                data: { numero_documento: documento },
            }).done(function (response) {
                let alerta = $('#resultado_busqueda_alerta');

                $('#resultado_busqueda_documento').show();
                $('#resultado_participante_id').val(response.data.id || '');
                $('#resultado_fecha').val((response.preview && response.preview.fecha) || '');
                $('#hidden_numero_factura').val((response.preview && response.preview.numero_factura) || '');
                $('#resultado_nombre_facturacion').val((response.preview && response.preview.nombre_facturacion) || '');
                $('#hidden_numero_documento').val(response.data.numero_documento || '');
                $('#hidden_numero_documento_facturacion').val((response.preview && response.preview.numero_documento_facturacion) || '');
                $('#hidden_correo_facturacion').val((response.preview && response.preview.correo_facturacion) || '');
                $('#hidden_telefono_facturacion').val((response.preview && response.preview.telefono_facturacion) || '');
                $('#resultado_nombre').val(response.data.nombre || '');
                $('#resultado_apellido').val(response.data.apellido || '');
                $('#resultado_genero').val(response.data.genero || '');

                alerta.removeClass('alert-danger alert-warning alert-success')
                    .addClass(response.can_update ? 'alert-success' : 'alert-warning')
                    .text(response.message);

                $('#btnBuscarDocumento').hide();

                if (response.can_update) {
                    $('#bloque_campos_actualizables').show();
                    $('#resultado_corral').val((response.preview && response.preview.corral) || '');
                    $('#resultado_talla').val((response.preview && response.preview.talla_camiseta) || '');
                    $('#resultado_correo').val((response.preview && response.preview.correo) || '');
                    $('#campo_1').val((response.preview && response.preview.valor_pagado !== undefined) ? parseFloat(response.preview.valor_pagado).toFixed(2) : '0.00');
                    $('#campo_2').val((response.preview && response.preview.total !== undefined) ? parseFloat(response.preview.total).toFixed(2) : '0.00');
                    $('#metodo_pago').val('');
                    $('#referencia').val('');
                    $('#btnGuardarAjuste').show();
                } else {
                    $('#bloque_campos_actualizables').hide();
                    $('#resultado_corral, #resultado_talla, #resultado_correo, #campo_1, #campo_2, #metodo_pago, #referencia').val('');
                    $('#btnGuardarAjuste').hide();
                }

                table.column(4).search(documento).draw();
            }).fail(function (xhr) {
                let mensaje = 'No se encontró información.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }

                $('#resultado_busqueda_documento').show();
                $('#resultado_participante_id').val('');
                $('#hidden_numero_documento, #hidden_numero_factura, #hidden_numero_documento_facturacion, #hidden_correo_facturacion, #hidden_telefono_facturacion').val('');
                $('#resultado_fecha, #resultado_nombre_facturacion, #resultado_nombre, #resultado_apellido, #resultado_genero').val('');
                $('#bloque_campos_actualizables').hide();
                $('#resultado_corral, #resultado_talla, #resultado_correo, #campo_1, #campo_2, #metodo_pago, #referencia').val('');
                $('#btnGuardarAjuste').hide();
                $('#btnBuscarDocumento').show();
                $('#resultado_busqueda_alerta')
                    .removeClass('alert-success alert-warning')
                    .addClass('alert-danger')
                    .text(mensaje);
            });
        });

        $('#btnGuardarAjuste').on('click', function () {
            let participanteId = $('#resultado_participante_id').val();
            let metodoPago = $('#metodo_pago').val();

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
                url: '{{ route('admin.ajuste.store') }}',
                method: 'POST',
                data: {
                    participante_id: participanteId,
                    metodo_pago: metodoPago,
                    referencia: $('#referencia').val().trim(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
            }).done(function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Ajuste generado',
                    text: response.message,
                    confirmButtonText: 'Aceptar'
                }).then(function () {
                    $('#buscarDocumentoModal').modal('hide');
                    window.location.reload();
                });
            }).fail(function (xhr) {
                let mensaje = 'No se pudo guardar el ajuste.';

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

        $('#buscarDocumentoModal').on('shown.bs.modal', function () {
            $('#buscar_numero_documento').trigger('focus');
        });

        $('#buscarDocumentoModal').on('hidden.bs.modal', function () {
            $('#buscar_numero_documento').val('');
            $('#resultado_busqueda_documento').hide();
            $('#resultado_participante_id').val('');
            $('#hidden_numero_documento, #hidden_numero_factura, #hidden_numero_documento_facturacion, #hidden_correo_facturacion, #hidden_telefono_facturacion').val('');
            $('#resultado_fecha, #resultado_nombre_facturacion, #resultado_nombre, #resultado_apellido, #resultado_genero, #resultado_corral, #resultado_talla, #resultado_correo, #campo_1, #campo_2, #metodo_pago, #referencia').val('');
            $('#bloque_campos_actualizables').hide();
            $('#btnGuardarAjuste').hide();
            $('#btnBuscarDocumento').show();
            $('#resultado_busqueda_alerta').removeClass('alert-danger alert-warning alert-success').text('');
            table.search('').columns().search('').draw();
        });

        $('#buscar_numero_documento').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btnBuscarDocumento').trigger('click');
            }
        });
    });
</script>
@endsection
