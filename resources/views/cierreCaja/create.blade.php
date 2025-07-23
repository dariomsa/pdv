@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        PROCESAR CIERRE DE CAJA
    </div>
    <div class="card-body">
        <form id="sc_form" action="{{ route("admin.cierrecaja.store") }}" role="form" method="POST" enctype="multipart/form-data" class="was-validated">
            @csrf

			<div class="container-fluid col-md-12">
				<div class="row">			
					<div class="form-group">
						<label for="exampleInputPassword1">
							Ingresa Fecha
						</label>
						<input type="date" min="2024-03-01"  class="form-control" name="fecha" id="fecha" aria-describedby="emailHelp" value="<?php echo date("Y-m-d");?>" required>
						<div class="valid-feedback">Correcto</div>
						<div class="invalid-feedback">* Requerido</div>
					</div>
				</div>
				<div class="row">
					<div class="form-group">
						@if(Session::has('flash_message'))
							<p class="alert alert-danger">{{ Session::get('flash_message') }}</p>
						@endif
					</div>
				
				</div>	
				<div class="row">					
					<div class="form-group">
						<button type="submit" class="btn btn-danger" onclick="cerrar()" id = "send_form">
							Procesar
						</button> 
						<button type="button" class="btn btn-secondary" onClick="history.go(-1);">
							Cancelar
						</button>					 
					</div>	
				</div>	
			</div>		
		</form>
	</div>
</div>	
<script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
<script>
	function cerrar()  {
	  event.preventDefault();

		Swal.fire({
		title: '¿Está seguro de cerrar la caja?',
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
</script>
@endsection