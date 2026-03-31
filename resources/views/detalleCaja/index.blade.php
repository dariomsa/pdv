@extends('layouts.admin')
@section('content')
@can('cierre_caja_create')
    <!-- <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.cierrecaja.create") }}">
                Procesar Cierre de Caja
            </a>
        </div>
    </div> -->
						
@endcan
<div class="card">
    <div class="card-header">
        Cierres de Caja
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
							<th width="10">

							</th>
							<th>
								# Cierre
							</th>
							<th>
								Punto de Venta
							</th>
							<th>
								Fecha
							</th>  
							<th>
								Secuencia
							</th> 
							<th>
								Tipo Inscripción
							</th> 
							<th>
								Monto Total
							</th> 
							<th>
								# Factura
							</th> 	
							<th>
								Tipo Pago
							</th> 
							<th>
								Referencia
							</th> 
							<th>
								Valor
							</th> 					
							
						</tr>
					</thead>
					<tbody>
						@foreach($cierrecaja as $key => $cierrecaja)
							<tr data-entry-id="{{ $cierrecaja->id }}">
								<td>

								</td>
								<td>
									{{ $cierrecaja->id ?? '' }}
								</td>
								<td>
									{{ $cierrecaja->name ?? '' }}
								</td>
								<td>
									{{ $cierrecaja->fecha ?? '' }}
								</td>
								<td>
									{{ $cierrecaja->secuencia ?? '' }}
								</td>
								
								<td>
									@if($cierrecaja->tipo_inscripcion == 1)
									GENERAL
									@elseif($cierrecaja->tipo_inscripcion == 2)
									VIP SILVER
									@elseif($cierrecaja->tipo_inscripcion == 3)
									VIP GOLD
									@elseif($cierrecaja->tipo_inscripcion == 4)
									DISCAPACITADOS 15K
									@elseif($cierrecaja->tipo_inscripcion == 10)
									MINI 15K RACE
									@endif		    
								
									</td>
								<td>
									{{ $cierrecaja->valor ?? 0 }}
								</td>
								<td>
								{{ substr(($cierrecaja->clave_accesso), 24, 15) ?? '' }}
								</td>							
								<td>
								{{ $cierrecaja->metodo_pago ?? 0 }}

								</td>
								<td>
								{{ $cierrecaja->referencia ?? 0 }}

								</td>
								<td>
								{{ $cierrecaja->monto ?? 0 }}

								</td>

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
    url: "{{ route('admin.inscripciones.massDestroy') }}",
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