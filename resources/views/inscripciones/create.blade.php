@extends('layouts.admin')
@section('content')



<div class="card">
    <div class="card-header">
        CREAR INSCRIPCIÓN
    </div>
	
    






    @if(session()->has('inscripcion_id'))
        @php
        
            $inscripcion = \App\Models\Inscripcion::with('participantes_temporal')->find(session('inscripcion_id'));
            $participantes = $inscripcion ? $inscripcion->participantes_temporal : collect();
       
        @endphp

        @if($participantes && $participantes->count())
            <div class="p-3  mb-4 rounded">
                <h5 class="celeste">Participantes Añadidos</h5>
                <div class="table-responsive">
                    <table id="tabla-participantes" class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombres</th>
                                <th>Documento</th>
                                <th>Género</th>
                                <th>Correo</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Talla Camiseta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($participantes as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $p->nombres }} {{ $p->apellidos }}</td>
                                <td>{{ $p->tipo_documento }} - {{ $p->numero_documento }}</td>
                                <td>{{ ucfirst($p->genero) }}</td>
                                <td>{{ $p->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->fecha_nacimiento)->format('d/m/Y') }}</td>
                                <td>{{ $p->talla }}</td>
                                <td>
                                    <form action="{{ route('admin.participantes.destroy', $p->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este participante?')">
                                        @csrf
                                        @method('DELETE')
                                        
                                      <!--  <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>-->
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                   <form action="{{ route('admin.inscripciones.resumen') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-success">Confirmar Inscripción</button>
	
	
  <button type="button" class="btn btn-primary" id="btnShowForm">
    Añadir otro participante
  </button>
  
</form>

                </div>
            </div>
        @endif
    @endif

<div id="formWrap" style="{{ session()->has('inscripcion_id') ? 'display:none;' : '' }}">
    
     <form method="POST" action="{{ route('admin.inscripciones.store') }}" enctype="multipart/form-data" class="was-validated">
        @csrf

        <div id="participants-container">
            <div class="participant-form border p-3 mb-4 rounded">
                <h5 class="celeste" >Información del Participante</h5>
                <div class="form-row">
                 <div class="form-group col-md-4">
    <label for="tipo_inscripcion">Tipo de Inscripción</label>
<select class="form-control" id="tipo_inscripcion" name="tipo_inscripcion">

  @foreach($tiposInscripcion as $tipo)
            <option value="{{ $tipo->id }}"
                {{ old('tipo_inscripcion') == $tipo->id ? 'selected' : '' }} data-carrera="{{ $tipo->carrera_id }}">
                {{ $tipo->nombre }}
            </option>
        @endforeach
</select>



	 <select name="subtipo_conadis15k" id="subtipo_conadis15k" class="form-control" style="display:none; margin-top:10px;">
								 <option value="COMPETENCIA">SILLA DE RUEDAS PASEO</option>
								 <option value="PASEO">SILLA DE RUEDAS COMPETENCIA</option>
								 <option value="HANDCYCLE">SILLA DE RUEDAS HANDCYCLE</option>
								 <option value="OTROS">OTRAS DISCAPACIDADES</option>
							     </select>
								 
								 <select name="subtipo_conadis21k" id="subtipo_conadis21k" class="form-control" style="display:none; margin-top:10px;">
							
								 <option value="OTROS">OTRAS DISCAPACIDADES</option>
							     </select>

</div>

                    <div class="form-group col-md-4"> 
                        <label for="tipo_documento">Tipo de Documento</label>
                      <select class="form-control" required name="tipo_documento" id="tipo_doc_participante" onChange="Cambio2()" >
                            <option value="C">Cédula</option>
                            <option value="E">Pasaporte</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numero_documento">Número de Documento</label>
                        <input autocomplete="off"  type="text" class="form-control" maxlength="10"  minlength="10" pattern=".{10,10}" name="numero_documento" id="documento"   placeholder="10 números" required>
							
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nombres">Nombres</label>
                        <input   autocomplete="off" type="text" class="form-control" name="nombres" required>
							<div class="valid-feedback">Correcto</div>
							<div class="invalid-feedback">* Requerido</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="apellidos">Apellidos</label>
                        <input  autocomplete="off" type="text" class="form-control" name="apellidos" required>
                        <div class="valid-feedback">Correcto</div>
							<div class="invalid-feedback">* Requerido</div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="nacionalidad">Nacionalidad</label>
                        <select class="form-control" name="nacionalidad" required>
    
    @foreach ($paises as $pais)
        <option value="{{ $pais->codigo }}">{{ $pais->nombre }}</option>
    @endforeach
</select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="genero">Género</label>
                        <select class="form-control" name="genero" id="genero" required>
                            <option value="">Seleccione una opción</option>
                              <option value="M">Masculino</option>
            <option value="F">Femenino</option>
                        </select>
                    </div>
                   <div class="form-group col-md-3">
  <label for="fecha_nacimiento">Fecha de Nacimiento</label>
  <input
    type="date"
    id="fecha_nacimiento"
    class="form-control"
    name="fecha_nacimiento"
    required
  >
</div>
  <div class="form-group col-md-3">
                    <label for="categoria">Categoría</label>
                    <input type="text" class="form-control" name="categoria"  readonly id="categoria_output">
                </div>

                </div>

              

                <h5 class="celeste">Información Adicional</h5>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="talla">Talla Camiseta</label>
                        <select class="form-control" name="talla" id="cami" required>
                        <option value="">Seleccione una opción</option>
                         
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="celular">Celular Participante</label>

                        	<div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text">
            EC +593
        </span>
    </div>
    <input
        type="text"
        name="celular"
        id="celular_participante"
        class="form-control"
        pattern="^0[0-9]{9}$"
        title="Debe comenzar con 0 y tener 10 dígitos"
        maxlength="10"
        minlength="10"
        required
        autocomplete="off"
        placeholder="09xxxxxxxx"
    >
</div>
                            </div> 
                    <div class="form-group col-md-4">
                        <label for="email">Email Participante</label>
                        <input autocomplete="off"  type="email" class="form-control" name="email" id="inscripcion_email"  required> 
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="direccion">Dirección</label>
                        <input  autocomplete="off" type="text" class="form-control" name="direccion" required>
                    </div>
                  
				  <div class="form-group col-md-3">
  <label for="provincia_select">Provincia</label>
  <select class="form-control" name="provincia" id="provincia_select" required>
    <option value="">Seleccione...</option>
  </select>
</div>

<div class="form-group col-md-3">
  <label for="canton_select">Cantón</label>
  <select class="form-control" name="ciudad" id="canton_select" required>
    <option value="">Seleccione...</option>
  </select>
</div>

<div class="form-group col-md-2">
  <label for="parroquia_select">Parroquia</label>
  <select class="form-control" name="parroquia" id="parroquia_select" required>
    <option value="">Seleccione...</option>
  </select>
</div>

                </div>
                

                <h5 class="celeste" >Contacto de Emergencia</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="emergencia_nombre">Nombre</label >
                        <input  autocomplete="off"  type="text" class="form-control" name="emergencia_nombre" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="emergencia_celular">Celular</label>

                      <div class="input-group">
    <div class="input-group-prepend">
        <span class="input-group-text">
            EC +593
        </span>
    </div>
    <input
        type="text"
        name="emergencia_celular"
        id="emergencia_celular"
        class="form-control"
        pattern="^0[0-9]{9}$"
        title="Debe comenzar con 0 y tener 10 dígitos"
        maxlength="10"
        minlength="10"
        required
        autocomplete="off"
        placeholder="09xxxxxxxx"
    >
</div>
                        </div> 
                </div>
            </div>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary" id="add-participant">Continuar</button>
   
        </div>
    </form>
</div></div>

{{-- LOADING OVERLAY --}}
<div id="globalLoading">
  <div class="loading-card">
    <div class="spinner"></div>
    <div class="loading-text">
      <div class="loading-title">Procesando…</div>
      <div class="loading-sub">Un momento por favor</div>
    </div>
  </div>
</div>

@push('scripts')
<script>
    document.getElementById('add-participant').addEventListener('click', function () {
        const container = document.getElementById('participants-container');
        const first = container.querySelector('.participant-form');
        const clone = first.cloneNode(true);

        // Limpiar valores de los inputs clonados
        Array.from(clone.querySelectorAll('input, select')).forEach(el => {
            el.value = '';
        });

        container.appendChild(clone);
    });
</script>
@endpush


  
</div>	


  <script>
    function myFunction(tipo) {
		var x = parseFloat(document.getElementById('forma_pago').value);
		var y = $( "#forma_pago option:selected" ).data("codigo")
		if (y == 'Tarjeta' || y == 'Transferencia' || y == 'Deposito' || y == 'Deuna'  ) {
			document.getElementById('num_referencia').disabled = false;
		} else {
			document.getElementById('num_referencia').value = ' ';
			document.getElementById('num_referencia').disabled = true;
		}
    };

	function DatosFactura()  {
		
		if( $('#confirmar').is(':checked') ){
		
			$("#numero_doc_facturacion").val(document.querySelector('#documento').value);
			$("#nombre_facturacion").val(document.querySelector("#inscripcion_nombre").value);
			$("#apellido_facturacion").val(document.querySelector("#inscripcion_apellido").value);
			$("#nombre_ruc_facturacion").val(document.querySelector("#inscripcion_nombre").value +' '+ document.querySelector("#inscripcion_apellido").value);

			$("#email_facturacion").val(document.querySelector("#inscripcion_email").value);
			$("#telefono_facturacion").val(document.querySelector("#celular_participante").value);	
			$("#direccion_facturacion").val(document.querySelector("#callePrincipal").value + ' ' + document.querySelector("#numeroDireccion").value + ' y ' + document.querySelector("#calleSecundaria").value);			
		} else {
			$("#numero_doc_facturacion").val('');
			$("#nombre_facturacion").val('');
			$("#apellido_facturacion").val('');
			$("#nombre_ruc_facturacion").val('');

			$("#email_facturacion").val('');
			$("#telefono_facturacion").val('');	
			$("#direccion_facturacion").val('');			
		}
	};
	
	function Cambio()  {
		$tipo = document.querySelector("#fact_tipo_documento").value;
		console.log($tipo);
		if ($tipo == 'C') {
			$("#numero_doc_facturacion").attr({maxlength:10, pattern:'.{10,10}',placeholder:'10 números'});
			$("#nombre_facturacion").prop('required',true);
			$("#apellido_facturacion").prop('required',true);
			$("#nombre_ruc_facturacion").prop('required',false);
			$("#show_nombre").show();
			$("#show_ruc").hide(); 
			
		}
 		if ($tipo == 'R') {
			$("#numero_doc_facturacion").attr({maxlength:13, pattern:'.{13,13}',placeholder:'13 números'});
			$("#nombre_facturacion").prop('required',false);
			$("#apellido_facturacion").prop('required',false);  
			$("#nombre_ruc_facturacion").prop('required',true);
			$("#show_nombre").hide();
			$("#show_ruc").show();
		}
 		if ($tipo == 'E') {
			$("#numero_doc_facturacion").attr({maxlength:15, pattern:'.{4,16}',placeholder:'4-16 caracteres'});
			$("#show_nombre").show();
			$("#show_ruc").hide();
			$("#nombre_facturacion").prop('required',true);
			$("#apellido_facturacion").prop('required',true);  
			$("#nombre_ruc_facturacion").prop('required',false); 
		}	 
	}

	function Cambio2()  {
		$tipo = document.querySelector("#tipo_doc_participante").value;
		console.log($tipo);
		if ($tipo == 'C') {
			$("#documento").val('');
			$("#documento").attr({maxlength:10, pattern:'.{10,10}',placeholder:'10 números'});
				
		}
 		if ($tipo == 'E') {
			$("#documento").val('');
			$("#documento").attr({minlength:4, maxlength:15, pattern:'.{4,16}',placeholder:'4-16 caracteres'});
			}	 
	}
	

	
  </script>
  
  
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const tipoSelect = document.getElementById('tipo_inscripcion');
    const conadis15k = document.getElementById('subtipo_conadis15k');
    const conadis21k = document.getElementById('subtipo_conadis21k');

    function toggleSubtipos() {
        const value = tipoSelect.value;

        // Ocultar ambos primero
        conadis15k.style.display = 'none';
        conadis21k.style.display = 'none';

        // Limpiar selección cuando se ocultan
        conadis15k.selectedIndex = 0;
        conadis21k.selectedIndex = 0;

        if (value == '4') {
            conadis15k.style.display = 'block';
        }

        if (value == '8') {
            conadis21k.style.display = 'block';
        }
    }

    // Ejecutar al cambiar
    tipoSelect.addEventListener('change', toggleSubtipos);

    // Ejecutar al cargar (por si viene old() seleccionado)
    toggleSubtipos();
});
</script>
  
<script>
document.addEventListener("DOMContentLoaded", function () {
  const inputFecha = document.querySelector('[name="fecha_nacimiento"]');
  const categoriaOutput = document.getElementById('categoria_output');
  const selectTipo = document.getElementById('tipo_inscripcion');

  function getCarreraId() {
    const opt = selectTipo?.options?.[selectTipo.selectedIndex];
    return opt ? parseInt(opt.dataset.carrera || '0', 10) : 0;
  }

  function consultarCategoria() {
    const fecha = inputFecha.value;
    if (!fecha) return;

    const carrera_id = getCarreraId();
    if (!carrera_id) {
      categoriaOutput.value = 'Sin categoría';
      return;
    }

    fetch('/admin/categoria/por-fecha', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        fecha_nacimiento: fecha,
        carrera_id: carrera_id
      })
    })
    .then(res => res.json())
    .then(data => {
      console.log(data);
      categoriaOutput.value = data?.categoria ? data.categoria : 'Sin categoría';
    })
    .catch(() => {
      categoriaOutput.value = 'Sin categoría';
    });
  }

  // cuando cambia la fecha
  inputFecha.addEventListener('change', consultarCategoria);

  // opcional: si cambias tipo_inscripcion, recalcula con la fecha ya ingresada
if (selectTipo) {
  selectTipo.addEventListener('change', function () {

    inputFecha.value = '';


    categoriaOutput.value = '';


  });
}
  
  
});




	  //Validar las cajas de texto...
        $('#emergencia_celular, #celular_participante').on('input', function (evt) {
				
    
        jQuery(this).val(jQuery(this).val().replace(/[^0-9]/g, ''));
      
          
        });

</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const inputDocumento = document.getElementById('documento');

    inputDocumento.addEventListener('change', function () {
        const numeroDocumento = this.value.trim();
        if (!numeroDocumento) return;

        fetch('/buscar/base2024', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ cedula: numeroDocumento })
        })
        .then(res => res.json())
        .then(data => {
            console.log(data);

            if (data.existe) {
                // Rellenar campos según el JSON recibido (ejemplo)
              document.querySelector('[name="nombres"]').value = data.nombres ?? '';
                document.querySelector('[name="apellidos"]').value = data.apellidos ?? '';
                document.querySelector('[name="fecha_nacimiento"]').value = data.fecha_nacimiento?.split(' ')[0] ?? '';
               document.querySelector('[name="email"]').value = data.email ?? '';
                document.querySelector('[name="celular"]').value = data.telefono ?? '';
                  document.querySelector('[name="categoria"]').value = data.categoria ?? '';
                // ... puedes agregar más campos
            } else {
                //alert('No se encontró registro en base2024');
            }
        })
        .catch(error => {
            console.error('Error al consultar base2024:', error);
        });
    });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {

  // Nacionalidad EXISTE, no la tocamos
  const $pais = document.querySelector('select[name="nacionalidad"]');

  const $provincia = document.getElementById('provincia_select');
  const $canton    = document.getElementById('canton_select');
  const $parroquia = document.getElementById('parroquia_select');

  function setOptions($select, items, placeholder){
    $select.innerHTML = '';
    const opt0 = document.createElement('option');
    opt0.value = '';
    opt0.textContent = placeholder || 'Seleccione...';
    $select.appendChild(opt0);

    (items || []).forEach(txt => {
      const opt = document.createElement('option');
      opt.value = txt;
      opt.textContent = txt;
      $select.appendChild(opt);
    });
  }

  async function loadProvincias(paisCodigo){
    setOptions($provincia, [], 'Cargando...');
    setOptions($canton, [], 'Seleccione...');
    setOptions($parroquia, [], 'Seleccione...');

    const res = await fetch(`/admin/geo/provincias?pais=${encodeURIComponent(paisCodigo)}`);
    const data = await res.json();

    setOptions($provincia, data || [], 'Seleccione provincia...');
  }

  async function loadCantones(paisCodigo, provincia){
    setOptions($canton, [], 'Cargando...');
    setOptions($parroquia, [], 'Seleccione...');

    const res = await fetch(`/admin/geo/cantones?pais=${encodeURIComponent(paisCodigo)}&provincia=${encodeURIComponent(provincia)}`);
    const data = await res.json();

    setOptions($canton, data || [], 'Seleccione cantón...');
  }

  async function loadParroquias(paisCodigo, provincia, canton){
    setOptions($parroquia, [], 'Cargando...');

    const res = await fetch(`/admin/geo/parroquias?pais=${encodeURIComponent(paisCodigo)}&provincia=${encodeURIComponent(provincia)}&canton=${encodeURIComponent(canton)}`);
    const data = await res.json();

    setOptions($parroquia, data || [], 'Seleccione parroquia...');
  }

  // Eventos
  $pais.addEventListener('change', () => {
    loadProvincias($pais.value || 'EC');
  });

  $provincia.addEventListener('change', () => {
    if(!$provincia.value){
      setOptions($canton, [], 'Seleccione...');
      setOptions($parroquia, [], 'Seleccione...');
      return;
    }
    loadCantones($pais.value || 'EC', $provincia.value);
  });

  $canton.addEventListener('change', () => {
    if(!$canton.value){
      setOptions($parroquia, [], 'Seleccione...');
      return;
    }
    loadParroquias($pais.value || 'EC', $provincia.value, $canton.value);
  });


  loadProvincias($pais.value || 'EC');

});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById('btnShowForm');
  const formWrap = document.getElementById('formWrap');

  if(btn && formWrap){
    btn.addEventListener('click', function(){
      formWrap.style.display = 'block';
      formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {

  const categoriasPorCarrera = {
    1: { mode: 'age',  min: 16 },                         // 15K
    2: { mode: 'age',  min: 18 },                         // 21K
    3: { mode: 'year', minYear: 2009, maxYear: 2019 }     // 5K
  };

  const selTipo  = document.getElementById('tipo_inscripcion');
  const inpFecha = document.getElementById('fecha_nacimiento');

  function pad2(n){ return String(n).padStart(2,'0'); }

  function setMinMaxFechaByCarrera(carreraId){
    const cfg = categoriasPorCarrera[Number(carreraId)];
    if (!cfg) return;

    let minDate = null;
    let maxDate = null;

    if (cfg.mode === 'age') {
      // Solo max por edad mínima
      const hoy = new Date();
      const d = new Date(hoy.getFullYear() - cfg.min, hoy.getMonth(), hoy.getDate());
      maxDate = `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;

      // Muy importante: si antes estuvo en mode year, quita el min
      inpFecha.removeAttribute('min');
    } else if (cfg.mode === 'year') {
      minDate = `${cfg.minYear}-01-01`;
      maxDate = `${cfg.maxYear}-12-31`;
    }

    if (minDate) inpFecha.min = minDate;
    if (maxDate) inpFecha.max = maxDate;

    // Si el valor actual queda fuera del rango, limpiar
    const v = inpFecha.value;
    if (v) {
      if ((minDate && v < minDate) || (maxDate && v > maxDate)) {
        inpFecha.value = '';
      }
    }
  }

  function carreraFromSelect(){
    const opt = selTipo.options[selTipo.selectedIndex];
    return opt ? (opt.dataset.carrera || null) : null;
  }

  // al cargar
  setMinMaxFechaByCarrera(carreraFromSelect());

  // al cambiar tipo_inscripcion
  selTipo.addEventListener('change', () => {
    setMinMaxFechaByCarrera(carreraFromSelect());
    // opcional: resetear la fecha al cambiar tipo
    // inpFecha.value = '';
  });

});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {

  const loadingEl = document.getElementById('globalLoading');
  let loadingTimer = null;
  let loadingCount = 0;

  function showLoading(){
    loadingCount++;
    if (!loadingEl) return;
    loadingEl.classList.add('show');
  }

  function hideLoading(){
    loadingCount = Math.max(0, loadingCount - 1);
    if (!loadingEl) return;
    if (loadingCount === 0) loadingEl.classList.remove('show');
  }

  // Para cambios rápidos, evitamos parpadeo
  function flashLoading(ms = 300){
    showLoading();
    clearTimeout(loadingTimer);
    loadingTimer = setTimeout(() => {
      hideLoading();
    }, ms);
  }

  // ✅ 1) Mostrar loading en cada change / input del form
  const formWrap = document.getElementById('formWrap');
  if (formWrap) {
    formWrap.addEventListener('change', function(e){
      const t = e.target;
      if (!t) return;

      // SOLO campos del formulario (inputs/selects/textarea)
      const isField = ['SELECT'].includes(t.tagName);
      if (!isField) return;

      flashLoading(300);
    });


  }

  // ✅ 2) Hook global: mostrar loading automáticamente en TODOS los fetch()
  // (tu categoria/por-fecha, buscar/base2024, geo/provincias, etc.)
  const originalFetch = window.fetch;
  window.fetch = function(...args){
    showLoading();
    return originalFetch(...args)
      .then(res => res)
      .catch(err => { throw err; })
      .finally(() => hideLoading());
  };

  // ✅ 3) Mostrar loading al enviar el form (Continuar)
  const form = formWrap ? formWrap.querySelector('form') : null;
  if (form) {
    form.addEventListener('submit', function(){
      showLoading();
    });
  }

});
</script>



<script>
$(function () {

  const $selTipo = $('#tipo_inscripcion'); // tipo_inscripcion (options vienen por AJAX)
  const $genero  = $('#genero');
  const $cami    = $('#cami');

  function getCarreraId() {
    const opt = $selTipo.find('option:selected');
    return opt.length ? parseInt(opt.data('carrera') || 0, 10) : 0;
  }

  function resetTallas(msg = 'Seleccione') {
    $cami.prop('disabled', true).empty()
      .append('<option value="" selected disabled>Seleccione género</option>');
  }

  async function cargarTallasDisponibles() {
    const carreraId = getCarreraId();
    const genero = $genero.val();

    console.log('cargarTallasDisponibles => carreraId:', carreraId, 'genero:', genero);

    if (!carreraId || !genero) {
      resetTallas('Seleccione');
      return;
    }

    resetTallas('Cargando...');

    $.ajax({
      url: '/admin/tallas',
      type: 'GET',
      dataType: 'json',
      data: { carrera_id: carreraId, genero: genero },
      success: function (data) {
        $cami.prop('disabled', false).empty()
          .append('<option value="" selected disabled>Seleccione</option>');

        if (Array.isArray(data) && data.length) {
          data.forEach(function(t){
            $cami.append('<option value="' + t + '">' + t + '</option>');
          });
        } else {
          resetTallas('Sin stock');
        }
      },
      error: function (xhr) {
        console.error('Error tallas:', xhr.status, xhr.responseText);
        resetTallas('Error al cargar');
      }
    });
  }

  // ✅ cuando cambie género -> cargar tallas
  $genero.on('change', cargarTallasDisponibles);

  // ✅ si cambia tipo_inscripcion -> limpia talla y (si ya hay genero) recarga
  $selTipo.on('change', function(){
    resetTallas('Seleccione');
    if ($genero.val()) cargarTallasDisponibles();
  });

  // ✅ al cargar la página: deja lista la talla (por si ya hay género seleccionado)
  resetTallas('Seleccione');
  if ($genero.val()) cargarTallasDisponibles();

});
</script>





@endsection

