@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
       FACTURACIÓN
    </div>
 <div class="p-3  mb-4 rounded">
 <h5 class="celeste">Inscripciones</h5>

@if ($errors->any())
    <div role="alert">
        <div class="bg-red-500 text-white font-bold rounded-t px-4 py-2 mx-4">
            Validation Errors
        </div>
        <div class="border border-t-0 border-red-400 rounded-b bg-red-100 px-4 py-3 text-red-700 mx-4">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($inscripcion && $inscripcion->participantes_temporal->count())
    
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
                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

    <form action="{{ route('admin.inscripciones.finalizar') }}" method="POST">
        @csrf

 <div class="row">
 
     <div class="col-md-7">
         <h5 class="celeste">Datos de facturación</h5>
        <div class="form-check mb-2">
            <input type="checkbox" class="form-check-input" id="usar_mismos" onclick="rellenarDatos()">
            <label class="form-check-label" for="usar_mismos">Desea utilizar los mismos datos de inscripción</label>
        </div>

        <div class="form-row">
            <div class="form-group col-md-3">
                <label>Tipo de Documento</label>
                <select name="fact_tipo_documento" class="form-control">
                    <option value="C">Cédula</option>
                    <option value="R">RUC</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label>Documento</label>
                <input type="text" name="numero_doc_facturacion" class="form-control" id="documento_fact" value="{{ old('documento', $primer->numero_documento ?? '') }}">
            </div>
            <div class="form-group col-md-3">
                <label>Nombre</label>
                <input type="text" name="nombre_facturacion" class="form-control" id="nombre_fact" value="{{ old('nombre', $primer->nombres ?? '') }}">
            </div>
            <div class="form-group col-md-3">
                <label>Apellido</label>
                <input type="text" name="apellido_facturacion" class="form-control" id="apellido_fact" value="{{ old('apellido', $primer->apellidos ?? '') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Email</label>
                <input type="email" name="email_facturacion" class="form-control" id="email_fact" value="{{ old('email', $primer->email ?? '') }}">
            </div>
            <div class="form-group col-md-4">
                <label>Celular</label>
                <input type="text" name="telefono_facturacion" class="form-control" id="celular_fact" value="{{ old('celular', $primer->celular ?? '') }}">
            </div>
            <div class="form-group col-md-4">
                <label>Dirección</label>
                <input type="text" name="direccion_facturacion" class="form-control" id="direccion_fact" value="{{ old('direccion', $primer->direccion ?? '') }}">
            </div>
        </div>

        <div class="form-group">
            <label>Nota Adicional</label>
            <textarea name="nota_facturacion" class="form-control" rows="2">{{ old('nota') }}</textarea>
        </div>  
    
    </div>
		
		    <div class="col-md-5">

      <h5 class="celeste">Resumen de Pago</h5>
        <table class="table ">
            <tr><th>SubTotal</th><td>${{ number_format($subtotal, 2) }}</td></tr>
            <tr><th>IVA</th><td>${{ number_format($iva_total, 2) }}</td></tr>
            <tr><th>Total</th><td><strong>${{ number_format($total, 2) }}</strong></td></tr>
        </table>

        <h5 class="celeste">Forma de pago</h5>
       <select name="forma_pago" class="form-control w-50" id="forma_pago_select">
    @foreach ($formasPago as $fp)
          <option value="{{ $fp->id }}" data-metodo="{{ strtolower($fp->metodo_pago) }}">
            {{ $fp->metodo_pago }}
        </option>
    @endforeach
</select>
</br>
        <div class="form-group w-50" id="referencia_group">
                <label>Referencia</label>
             
                      <input type="text" name="referencia" class="form-control" id="">

            </div>

  
        

        <button type="submit" class="btn btn-primary">Finalizar Inscripción</button>
		</div>
    </form>
</div>
</div>
@endif

<script>
function rellenarDatos() {
    const participante = @json($primer ?? []);
    if (document.getElementById('usar_mismos').checked && participante) {
        document.getElementById('documento_fact').value = participante.numero_documento || '';
        document.getElementById('nombre_fact').value = participante.nombres || '';
        document.getElementById('apellido_fact').value = participante.apellidos || '';
        document.getElementById('email_fact').value = participante.email || '';
        document.getElementById('celular_fact').value = participante.celular || '';
        document.getElementById('direccion_fact').value = participante.direccion || '';
    }
}
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById('forma_pago_select');
    const referenciaGroup = document.getElementById('referencia_group');

    function toggleReferencia() {
        const selectedOption = select.options[select.selectedIndex];
        const metodo = selectedOption.getAttribute('data-metodo');

        if (metodo === 'efectivo') {
            referenciaGroup.style.display = 'none';
        } else {
            referenciaGroup.style.display = 'block';
        }
    }

    select.addEventListener('change', toggleReferencia);

    // Ejecutar al cargar la página también
    toggleReferencia();
});
</script>

@endsection
