@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        Reporte de Ventas Generales
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
							<th>
								{{ trans('cruds.expense.fields.id') }}
							</th>
							<th>
								Fecha
							</th>
							<th>
								Número de Factura
							</th>
						
						
							<th>
							Empresa / Fact.
							</th>
							<th>
								Teléfono
							</th>								
							<th>
								Origen
							</th>							
							<th>
								Tipo de Inscripción
							</th>		
							<th>
								Tipo de Documento
							</th>		
							<th>
								Número Documento
							</th>		
							<th>
								Nombre Participante
							</th>		
							<th>
								Apellido Participante
							</th>	
							<th>
								Genero
							</th>							
							<th>
								Corral
							</th>		
							<th>
								Categoria
							</th>	
							<th>
								Fecha Nacimiento
							</th>	
							<th>
								Talla Camiseta
							</th>							
							<th>
								Correo Participante
							</th>
							<th>
								Método de Pago
							</th>
							<th>
								Referencia
							</th>								
							<th>
								Sub Total
							</th>	
							<th>
								IVA
							</th>	
							<th>
								Total
							</th>	
						 <th>
								DISCAPACIDAD
							</th>	
								<!--<th>
								CORTESIA
							</th>					 -->
						</tr>
					</thead>
					<tbody>
						@foreach($data as $key => $data)
							<tr>	
								<td>

								</td>
								<td>
									{{ $data->fecha ?? '' }}
								</td>						
								<td>
									{{ $data->clave_accesso ?? '' }}
								</td>
							
							
								<td>
									{{ $data->nombre_ruc_facturacion ?? '' }}
								</td>
								<td>
									{{ $data->celular_participante ?? '' }}
								</td>										
								<td>
									{{ $data->origen ?? '' }}-{{ $data->wh ?? '' }}
								</td>								
								<td>
									{{ $data->carrera ?? '' }}
								</td>						
								<td>
									{{ $data->fact_tipo_documento ?? '' }}
								</td>	
								<td>
									{{ $data->numero_doc_participante ?? '' }}
								</td>						
								<td>
									{{ $data->nombre1_participante ?? '' }}
								</td>						
								<td>
									{{ $data->apellido1_participante ?? '' }}
								</td>
								<td>
									{{ $data->genero ?? '' }}
								</td>								
								<td>
									{{ $data->corral ?? '' }}
								</td>
								<td>
									{{ $data->categoria ?? '' }}
								</td>
								<td>
									{{ $data->fecha_nacimiento ?? '' }}
								</td>

								<td>
									{{ $data->camiseta_participante ?? '' }}
								</td>								
								<td>
									{{ $data->email_participante ?? '' }}
								</td>						
								<td>
									{{ $data->metodo_pago ?? '' }}
								</td>
								<td>
									{{ $data->referencia ?? '' }}
								</td>								
								<td>
									{{ $data->subtotal ?? '' }}
								</td>	
								<td>
									{{ $data->iva ?? '' }}
								</td>						
								<td>
									{{ $data->total ?? '' }}
								</td>	
								<td>
									{{ $data->discap ?? '' }}
								</td>	
								<!-- <td>
									{{ $data->gratis ?? '' }}
								</td>	 -->

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