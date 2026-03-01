@extends('layouts.admin')
@section('content')
@can('inscripcion_create')
  <div style="margin-bottom: 10px;" class="row">
        <div class="col-lg-12">
            <a class="btn btn-success" href="{{ route('admin.corporativas.create') }}">
                Añadir Corporativas
            </a>
        </div>
    </div>
		@endcan
<div class="card">

			           @if(Session::has('flash_message'))
							<div class="alert alert-danger">{{ Session::get('flash_message') }}</div>
						@endif
                        @if(Session::has('success'))
							<div class="alert alert-success">{{ Session::get('success') }}  <a target="_blank" href="/facturar/{{ Session::get('establecimiento') }}">
                           <i class="nav-icon fas fa-fw fa-print" style="font-size:25px"></i></a></div> 
					 
						
						@endif
      
      
    <div class="card-header">
       <strong> Inscripciones Corporativas </strong>
    </div>

		<div class="card-body">
    <form id="sc_form" method="get">
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
                    <option value="5" {{ (isset($carrera) && $carrera == '5') ? 'selected' : '' }}>Quito 21K Race</option>
                </select>
            </div>

            <div class="col-4 col-md-2">
                <label class="control-label">&nbsp;</label><br>
                <button class="btn btn-primary w-100" type="submit">Filtrar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover datatable datatable-Expense">
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
						<th style="min-width:160px;">ACCIONES</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($participantes as $participante)
                        <tr data-entry-id="{{ $participante->id }}">
                            <td>{{ $participante->id }}</td>

                            <td class="" data-id="{{ $participante->id }}" data-column="created_at">
                                {{ $participante->created_at }}
                            </td>

                            <td class="" data-id="{{ $participante->id }}" data-column="factura_numero">
                                {{ $participante->factura_numero ?? 'ND' }}
                            </td>

                            <td class="" data-id="{{ $participante->id }}" data-column="empresa_factura">
                                {{ $participante->empresa_factura ?? 'ND' }}
                            </td>

                            <td class="" data-id="{{ $participante->id }}" data-column="telefono_factura">
                                {{ $participante->telefono_factura ?? 'ND' }}
                            </td>

                            <td class="" data-id="{{ $participante->id }}" data-column="origen">
                                {{ $participante->origen ?? 'ND' }}
                            </td>

                            {{-- Igual que normales: relación tipoInscripcion + etiqueta --}}
                            <td>
                                {{ $participante->tipoInscripcion->nombre ?? 'ND' }} 

                            <td class="editable" data-id="{{ $participante->id }}" data-column="tipo_documento">
                                {{ $participante->tipo_documento }}
                            </td>

                            {{-- EXCEPCIÓN: numero_documento NO editable --}}
                            <td>{{ $participante->numero_documento }}</td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="nombres">
                                {{ $participante->nombres }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="apellidos">
                                {{ $participante->apellidos }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="genero">
                                {{ $participante->genero }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="corral">
                                {{ $participante->corral ?? 'ND' }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="categoria">
                                {{ $participante->categoria }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="fecha_nacimiento">
                                {{ $participante->fecha_nacimiento }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="talla">
                                {{ $participante->talla }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="email">
                                {{ $participante->email }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="metodo_pago">
                                {{ $participante->metodo_pago ?? 'ND' }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="referencia">
                                {{ $participante->referencia ?? 'ND' }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="sub_total">
                                {{ $participante->sub_total ?? 'ND' }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="iva">
                                {{ $participante->iva ?? 'ND' }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="total">
                                {{ $participante->total ?? 'ND' }}
                            </td>

                            <td class="editable" data-id="{{ $participante->id }}" data-column="discapacidad">
                                {{ $participante->discapacidad ?? 'ND' }}
                            </td>
							
							
							<td class="text-end">
    <button type="button"
            class="btn btn-sm btn-dark btn-editar-fact"
            data-bs-toggle="modal"
            data-bs-target="#modalEditarFact"
            data-inscripcion-id="{{ $participante->inscripcion_id ?? '' }}"
            data-tipo-documento="{{ $participante->tipo_documento ?? 'R' }}"
            data-numero-documento="{{ $participante->numero_documento ?? '' }}"
            data-razon-social="{{ $participante->empresa_factura ?? '' }}"
            data-empresa="{{ $participante->empresa_factura ?? '' }}"
            data-email="{{ $participante->email ?? '' }}"
            data-telefono="{{ $participante->telefono_factura ?? '' }}"
            data-direccion="{{ $participante->direccion ?? '' }}"
            data-nota-adicional="{{ $participante->nota_adicional ?? '' }}"
            data-forma-pago="{{ $participante->forma_pago_id ?? '' }}"
            data-referencia="{{ $participante->referencia ?? '' }}"
            data-subtotal="{{ $participante->sub_total ?? '0.00' }}"
            data-iva="{{ $participante->iva ?? '0.00' }}"
			
			data-cert="{{ $participante->certificado ?? '0.00' }}"
            data-total="{{ $participante->total ?? '0.00' }}">
        Editar
    </button>

    <a href="javascript:void(0)"
   class="btn btn-sm btn-certificado {{ ($participante->certificado ?? 0) == 1 ? 'btn-success' : 'btn-danger' }}"
   data-inscripcion-id="{{ $participante->inscripcion_id }}"
   data-certificado="{{ $participante->certificado ?? 0 }}">CERTIFICADOS</a>
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

<script>
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.btn-certificado');
    if (!btn) return;

    const inscripcionId = btn.dataset.inscripcionId;
    const certificado = parseInt(btn.dataset.certificado || '0', 10);

    if (certificado === 1) return;

    // UX loading
    btn.classList.remove('btn-danger');
    btn.classList.add('btn-secondary');
    const textoOriginal = btn.textContent;
    btn.textContent = 'PROCESANDO...';
    btn.style.pointerEvents = 'none';

    try {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ✅ Ruta real (con /admin) obtenida desde Laravel
        const endpoint = `{{ route('admin.corporativas.inscripcion', ['id' => '__ID__']) }}`.replace('__ID__', inscripcionId);

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Error al emitir certificados');
        }

        // ✅ OK → verde
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-success');
        btn.dataset.certificado = "1";
        btn.textContent = 'CERTIFICADO OK';

    } catch (error) {
        console.error(error);

        // ❌ Error → rojo
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-danger');
        btn.textContent = textoOriginal;
        btn.style.pointerEvents = 'auto';

        alert(error.message || 'No se pudo emitir el certificado');
        return;
    }

    btn.style.pointerEvents = 'auto';
});
</script>


@endsection