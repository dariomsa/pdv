@extends('layouts.admin')
@section('content')
@can('inscripcion_create')
  <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.inscripciones_gratuitas.create') }}">
                Añadir Participante Gratuito
            </a>
        </div>
    </div>
		@endcan
<div class="card">

			           @if(Session::has('flash_message'))
							<div class="alert alert-danger">{{ Session::get('flash_message') }}</div>
						@endif
                        @if(Session::has('success'))
							<div class="alert alert-success">{{ Session::get('success') }}  <a target="_blank" href="/admin/recibo?id={{ Session::get('id_ins') }}">
                           <i class="nav-icon fas fa-fw fa-print" style="font-size:25px"></i></a></div> 
					 
						
						@endif
      
      
    <div class="card-header">
       <strong> Inscripciones </strong>
    </div>

		<div class="card-body">
			<form id="sc_form" method="get">
				<div class="row">
					<div class="col-12 col-md-2">
						<label><small>FECHA DESDE</small></label>
						<input type="date" name="fecha_desde" class="form-control " value="{{$fecha_desde}}">
					</div>
					<div class="col-12 col-md-2">
						<label><small>FECHA HASTA</small></label>
						<input type="date" name="fecha_hasta" class="form-control " value="{{$fecha_hasta}}">
					</div>	
					<div class="col-4">
						<label class="control-label">&nbsp;</label><br>
						<button class="btn btn-primary" type="submit">Filtrar</button>
					</div>				
				</div>	
				<div class="table-responsive">
					<table class=" table table-bordered table-striped table-hover datatable datatable-Expense">
						<thead>
						 
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Número de Factura</th>
                    <th>Empresa / Fact.</th>
                    <th>Teléfono</th>
                    <th>Origen</th>
                    <th>Tipo de Inscripción</th>
                    <th>Tipo</th>
                    <th>Número Documento</th>
                    <th>Nombre Participante</th>
                    <th>Apellido Participante</th>
                    <th>Género</th>
                    <th>Corral</th>
                    <th>Categoría</th>
                    <th>Fecha Nacimiento</th>
                    <th>Talla Camiseta</th>
                    <th>Correo Participante</th>
                    <th>Método de Pago</th>
                    <th>Referencia</th>
                    <th>Sub Total</th>
                    <th>IVA</th>
                    <th>Total</th>
                    <th>DISCAPACIDAD</th>
                </tr>
            </thead>
					
				  <tbody>

		            @foreach($participantes as $participante)

                    <tr>
                        <td>{{ $participante->id }}</td>
                        <td>{{ $participante->created_at }}</td>
                        <td>{{ $participante->factura_numero ?? 'ND' }}</td>
                        <td>{{ $participante->empresa_factura ?? 'ND' }}</td>
                        <td>{{ $participante->telefono_factura ?? 'ND' }}</td>
                        <td>{{ $participante->origen ?? 'ND' }}</td>
                        <td>{{ $participante->tipoInscripcion->nombre ?? 'ND' }} - {{ $participante->factura }} </td>
                        <td>{{ $participante->tipo_documento }}</td>
                        <td>{{ $participante->numero_documento }}</td>
                        <td>{{ $participante->nombres }}</td>
                        <td>{{ $participante->apellidos }}</td>
                        <td>{{ $participante->genero }}</td>
                        <td>{{ $participante->corral ?? 'ND' }}</td>
                        <td>{{ $participante->categoria }}</td>
                        <td>{{ $participante->fecha_nacimiento }}</td>
                        <td>{{ $participante->talla }}</td>
                        <td>{{ $participante->email }}</td>
                        <td>{{ $participante->metodo_pago ?? 'ND' }}</td>
                        <td>{{ $participante->referencia ?? 'ND' }}</td>
                        <td>{{ $participante->sub_total ?? 'ND' }}</td>
                        <td>{{ $participante->iva ?? 'ND' }}</td>
                        <td>{{ $participante->total ?? 'ND' }}</td>
                        <td>{{ $participante->discapacidad ?? 'ND' }}</td>
                    </tr>
                @endforeach
            </tbody>
					</table>
				</div>
			</form>

    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
    $(function () {
  let dtButtons = $.extend(true, [], $.fn.dataTable.defaults.buttons)
@can('expense_delete')
  let deleteButtonTrans = '{{ trans('global.datatables.delete') }}'
  let deleteButton = {
    text: deleteButtonTrans,
    url: "#",
    className: 'btn-danger',
    action: function (e, dt, node, config) {
      var ids = $.map(dt.rows({ selected: true }).nodes(), function (entry) {
          return $(entry).data('entry-id')
      });

      if (ids.length === 0) {
        alert('{{ trans('global.datatables.zero_selected') }}')

        return
      }

      if (confirm('{{ trans('global.areYouSure') }}')) {
        $.ajax({
          headers: {'x-csrf-token': _token},
          method: 'POST',
          url: config.url,
          data: { ids: ids, _method: 'DELETE' }})
          .done(function () { location.reload() })
      }
    }
  }
  dtButtons.push(deleteButton)
@endcan

  $.extend(true, $.fn.dataTable.defaults, {
    order: [[ 1, 'desc' ]],
    pageLength: 100,
  });
  $('.datatable-Expense:not(.ajaxTable)').DataTable({ buttons: dtButtons })
    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e){
        $($.fn.dataTable.tables(true)).DataTable()
            .columns.adjust();
    });
})

</script>
@endsection