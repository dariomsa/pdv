@extends('layouts.admin')
@section('content')



<div class="card">
    <div class="card-header">
        CREAR INSCRIPCIÓN GRATUITA
    </div>
	
    
 @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif



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
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-4">
                   <form action="{{ route('admin.inscripciones_gratuitas.resumen') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-success">Confirmar Inscripción</button>
</form>

                </div>
            </div>
        @endif
    @endif

    
     <form method="POST" action="{{ route('admin.inscripciones_gratuitas.store') }}" enctype="multipart/form-data" class="was-validated">
        @csrf

        <div id="participants-container">
            <div class="participant-form border p-3 mb-4 rounded">
                <h5 class="celeste" >Información del Participante</h5>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="tipo_inscripcion">Tipo de Inscripción</label>
                        <select class="form-control" name="tipo_inscripcion">
                            <option value="1">GENERAL</option>
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
                        <input type="text" class="form-control" name="nombres" required>
							<div class="valid-feedback">Correcto</div>
							<div class="invalid-feedback">* Requerido</div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="apellidos">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" required>
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
                        <select class="form-control" name="genero" required>
                            <option value="">Seleccione una opción</option>
                              <option value="M">Masculino</option>
            <option value="F">Femenino</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                         <input type="date" class="form-control" max="2010-01-01" name="fecha_nacimiento" required>
                    </div>
  <div class="form-group col-md-3">
                    <label for="categoria">Categoría</label>
                    <input type="text" class="form-control" name="categoria" readonly>
                </div>

                </div>

              

                <h5 class="celeste">Información Adicional</h5>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="talla">Talla Camiseta</label>
                        <select class="form-control" name="talla" required>
                        <option value="">Seleccione una opción</option>
                         @foreach ($tallasDisponibles as $item)
                          <option value="{{ $item->talla }}">{{ $item->talla }}</option>
                         @endforeach
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
                        <input type="text" class="form-control" name="direccion" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="provincia">Provincia</label>
                        <input type="text" class="form-control" name="provincia" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="ciudad">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad" required>
                    </div>
                    <div class="form-group col-md-2">
                    <label for="parroquia">Parroquia</label>
                    <input type="text" class="form-control" name="parroquia" required>
                </div>
                </div>
                

                <h5 class="celeste" >Contacto de Emergencia</h5>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="emergencia_nombre">Nombre</label >
                        <input type="text" class="form-control" name="emergencia_nombre" required>
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
            <button type="submit" class="btn btn-primary" id="add-participant">+ Añadir Participante</button>
   
        </div>
    </form>
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
<script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>


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
	
	function cerrar()  {
	  event.preventDefault();

		Swal.fire({
		title: '¿Está seguro de crear y factura inscripción?',
		type: 'warning',
		showCancelButton: true,
		confirmButtonText: 'Si',
		cancelButtonText: "No",
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
	  }).then((result) => {
		if (result.value) {
	   $('#sc_form').submit();
		}
		return false;
	  })
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
  <link rel="stylesheet" href="/resources/demos/style.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>


   <script>
  $( function() {
    $( "#inscripcion_nacimiento" ).datepicker(
	
	{
        dateFormat: 'yy-mm-dd',
		 language: 'es',
        changeMonth: true,
        changeYear: true,
		 yearRange: "1900:2018",
        minDate: new Date(1900, 10 - 1, 25),
        maxDate: new Date(2009, 03, 10),
        inline: true
    }
	);
	
	
	
  } );
  </script>
  
  <script>
 $.datepicker.regional['es'] = {
 closeText: 'Cerrar',
 prevText: '< Ant',
 nextText: 'Sig >',
 currentText: 'Hoy',
 monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
 monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
 dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
 dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
 dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
 weekHeader: 'Sm',
 dateFormat: 'dd/mm/yy',
 firstDay: 1,
 isRTL: false,
 showMonthAfterYear: false,
 yearSuffix: ''
 };
 $.datepicker.setDefaults($.datepicker.regional['es']);
$(function () {
$("#fecha").datepicker();
});
</script>
@endsection

