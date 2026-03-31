@extends('layouts.admin')
@section('content')
@can('inscripcion_create')
  <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.inscripciones.create') }}">
                Añadir Participante
            </a>
        </div>
    </div>
	@endcan
<div class="card">

			           @if(Session::has('flash_message'))
							<div class="alert alert-danger">{{ Session::get('flash_message') }}</div>
						@endif
                        @if(Session::has('recibo'))
							<div class="alert alert-success">{{ Session::get('recibo') }}  <a target="_blank" href="/admin/recibo?id={{ Session::get('recibo') }}">
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
        <input type="date" name="fecha_desde" class="form-control" value="{{$fecha_desde}}">
    </div>

    <div class="col-12 col-md-2">
        <label><small>FECHA HASTA</small></label>
        <input type="date" name="fecha_hasta" class="form-control" value="{{$fecha_hasta}}">
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

    <div class="col-4 col-md-2">
        <label class="control-label">&nbsp;</label><br>
        <button class="btn btn-primary w-100" type="submit">Filtrar</button>
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
					    <th></th>
                </tr>
            </thead>
					
			<tbody>
@foreach($participantes as $participante)
<tr data-entry-id="{{ $participante->id }}">
    <td>{{ $participante->id }}</td>

    <td  class="" data-id="{{ $participante->id }}" data-column="created_at">
        {{ $participante->created_at }}
    </td>

    <td class="" data-id="{{ $participante->id }}" data-column="factura_numero">
        {{ $participante->factura_numero ?? 'ND' }}
    </td>

    <td  class="" data-id="{{ $participante->id }}" data-column="empresa_factura">
        {{ $participante->empresa_factura ?? 'ND' }}
    </td>

    <td  class="" data-id="{{ $participante->id }}" data-column="telefono_factura">
        {{ $participante->telefono_factura ?? 'ND' }}
    </td>

    <td class="" data-id="{{ $participante->id }}" data-column="origen">
        {{ $participante->origen ?? 'ND' }}
    </td>

    {{-- OJO: tipoInscripcion->nombre suele venir de relación; no lo hacemos editable --}}
    <td>
        {{ $participante->tipoInscripcion->nombre ?? 'ND' }} - {{ $participante->factura }}
    </td>

    <td class="editable" data-id="{{ $participante->id }}" data-column="tipo_documento">
        {{ $participante->tipo_documento }}
    </td>

    {{-- EXCEPCIÓN: numero_documento NO editable --}}
    <td>{{ $participante->numero_documento }}</td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="nombres">
        {{ $participante->nombres }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="apellidos">
        {{ $participante->apellidos }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="genero">
        {{ $participante->genero }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="corral">
        {{ $participante->corral ?? 'ND' }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="categoria">
        {{ $participante->categoria }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="fecha_nacimiento">
        {{ $participante->fecha_nacimiento }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="talla">
        {{ $participante->talla }}
    </td>

    <td contenteditable="true" class="editable" data-id="{{ $participante->id }}" data-column="email">
        {{ $participante->email }}
    </td>

    <td class="editable" data-id="{{ $participante->id }}" data-column="metodo_pago">
        {{ $participante->metodo_pago ?? 'ND' }}
    </td>

    <td  class="editable" data-id="{{ $participante->id }}" data-column="referencia">
        {{ $participante->referencia ?? 'ND' }}
    </td>

    <td  class="editable" data-id="{{ $participante->id }}" data-column="sub_total">
        {{ $participante->sub_total ?? 'ND' }}
    </td>

    <td class="editable" data-id="{{ $participante->id }}" data-column="iva">
        {{ $participante->iva ?? 'ND' }}
    </td>

    <td class="editable" data-id="{{ $participante->id }}" data-column="total">
        {{ $participante->total ?? 'ND' }}
    </td>

    <td  class="editable" data-id="{{ $participante->id }}" data-column="discapacidad">
        {{ $participante->discapacidad ?? 'ND' }}
    </td>
	<td >
       <a target="_blank" href="/admin/recibo?id={{ $participante->id }}">
                           <i class="nav-icon fas fa-fw fa-print" style="font-size:25px"></i></a>
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

@push('styles')
<style>
  .editable{ cursor:pointer }
  .editable:focus{ background:#fff7e6; outline:2px solid #ffc107; border-radius:4px }
  .saving{ background:#e6f7ff !important; }
  .error-cell{ background:#ffe6e6 !important; }
</style>
@endpush

@section('scripts')
@parent
<script>
$(function () {
 



  // Evita que Enter agregue salto de línea y en su lugar dispare guardado
  $(document).on('keydown', '.editable', function(e){
    if(e.key === 'Enter'){
      e.preventDefault();
      $(this).blur();
    }
  });

  // Guardar al perder foco
  $(document).on('blur', '.editable', function(){
    const $cell = $(this);
    const id = $cell.data('id');
    const column = $cell.data('column');
    const raw = $cell.text().trim();

    // Normalizar "ND" a null
    const value = (raw === 'ND' || raw === '') ? null : raw;

    $cell.addClass('saving');

    $.ajax({
      url: "{{ route('admin.inscripciones.update.inline') }}",
      method: "POST",
      data: {
        _token: "{{ csrf_token() }}",
        id: id,
        column: column,
        value: value
      }
    })
    .done(function(resp){
      if(resp.success){
        $cell.removeClass('error-cell').removeClass('saving');
        // Si backend devolvió value normalizado, úsalo
        if (typeof resp.value !== 'undefined') {
          $cell.text(resp.value ?? 'ND');
        } else {
          $cell.text(value ?? 'ND');
        }
        if(window.toastr){ toastr.success('Actualizado'); }
      }else{
        $cell.addClass('error-cell').removeClass('saving');
        if(window.toastr){ toastr.error(resp.message || 'No se pudo actualizar'); }
      }
    })
    .fail(function(xhr){
      $cell.addClass('error-cell').removeClass('saving');
      if(window.toastr){ toastr.error('Error del servidor'); }
    });
  });

  // Fix de redimensionar al cambiar de pestañas
  $('a[data-toggle="tab"]').on('shown.bs.tab', function(){
    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
  });
});
</script>
@endsection





