@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        Reporte de Ventas Generales
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
				
				   <div class="col-12 col-md-2">
        <label><small>CARRERA</small></label>
        <select name="carrera" class="form-control">
            <option value="">-- Todas --</option>
             <option value="1" {{ (isset($carrera) && $carrera == '1') ? 'selected' : '' }}>Quito 15K Race</option>
            <option value="2" {{ (isset($carrera) && $carrera == '2') ? 'selected' : '' }}>Quito 21K Race</option>
			<option value="3" {{ (isset($carrera) && $carrera == '3') ? 'selected' : '' }}>Mini 5K Race</option>
        </select>
    </div>
	
	
                <div class="col-4">
                    <label class="control-label">&nbsp;</label><br>
                    <button class="btn btn-primary" type="submit">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="table-responsive mt-4">
            <table class="table table-bordered table-striped table-hover datatable datatable-ventas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Número de Factura</th>
                        <th>Empresa / Factura</th>
					
                        <th>Celular Part.</th>
							 <th>Nota</th>
                        <th>Origen</th>
                        <th>Tipo de Inscripción</th>
                        <th>Tipo de Documento</th>
                        <th>Número Documento</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Género</th>
                        <th>Corral</th>
                        <th>Categoría</th>
                        <th>Fecha Nacimiento</th>
                        <th>Talla Camiseta</th>
                        <th>Correo</th>
                        <th>Método de Pago</th>
                        <th>Referencia</th>
                        <th>Sub Total</th>
                        <th>IVA</th>
                        <th>Total</th>
                        <th>Discapacidad</th>
						 <th>Subtipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $key => $item)
                        <tr>
                            <td>{{ $item['id'] }}</td>
                            <td>{{ $item['created_at'] }}</td>
                            <td>{{ $item['factura_numero'] }}</td>
                            <td>{{ $item['empresa_factura'] }}</td>
                            <td>{{ $item['telefono_factura'] }}</td>   
							<td>{{ $item['nota_facturacion'] }}</td>
                            <td>{{ $item['origen'] }}</td>
                            <td>{{ $item['tipoInscripcion']['nombre'] ?? 'ND' }}</td>
                            <td>{{ $item['tipo_documento'] }}</td>
                            <td>{{ $item['numero_documento'] }}</td>
                            <td>{{ $item['nombres'] }}</td>
                            <td>{{ $item['apellidos'] }}</td>
                            <td>{{ $item['genero'] }}</td>
                            <td>{{ $item['corral'] }}</td>
                            <td>{{ $item['categoria'] }}</td>
                            <td>{{ $item['fecha_nacimiento'] }}</td>
                            <td>{{ $item['talla'] }}</td>
                            <td>{{ $item['email'] }}</td>
                            <td>{{ $item['metodo_pago'] }}</td>
                            <td>{{ $item['referencia'] }}</td>
                            <td>{{ $item['sub_total'] }}</td>
                            <td>{{ $item['iva'] }}</td>
                            <td>{{ $item['total'] }}</td>
                            <td>{{ $item['discapacidad'] }}</td>
							 <td>{{ $item['subtipo'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
$(function () {
    let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons);

    $.extend(true, $.fn.dataTable.defaults, {
        order: [[1, 'desc']],
        pageLength: 100,
    });

    $('.datatable-ventas:not(.ajaxTable)').DataTable({ buttons: dtButtons });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
    });
});
</script>
@endsection
