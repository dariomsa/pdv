@extends('layouts.admin')
@section('content')

    <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route("admin.cierrecaja.create") }}">
                Procesar Cierre de Caja
            </a>
        </div>
    </div>
						

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
								{{ trans('cruds.expense.fields.id') }}
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
								Monto Total
							</th> 
							<th>
								# Facturas
							</th> 						
							<th>
								&nbsp;
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
									{{ $cierrecaja->monto_total ?? 0 }}
								</td>
								<td>
									{{ $cierrecaja->numero_facturas ?? 0 }}
								</td>							
								<td>
									
										<a class="btn btn-xs btn-primary" href="{{ route('admin.cierrecaja.show', $cierrecaja->id) }}">
											{{ trans('global.view') }}
										</a>
									

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