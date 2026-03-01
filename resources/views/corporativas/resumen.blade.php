@extends('layouts.admin')
@section('content')

<div class="card">
  <div class="card-header">
       CREAR INSCRIPCIONES CORPORATIVAS
    </div>
 <div class="p-3  mb-4 rounded">


@if(session('error'))
    <div class="alert alert-danger">{{ session('error')  }}  <a href="/admin/corporativas" >  <button class="btn btn-secondary" >Cancelar Inscripcion  </button></a></div>
@endif


@if(session('success'))
    <div class="alert alert-success">{{ session('success')  }}  <a href="/admin/corporativas" >  <button class="btn btn-secondary" >Cancelar Inscripcion  </button></a></div>
@endif

@if($inscripcion && $participantes->count())
    
 <div class="alert alert-success">Total : {{ $participantes->count() }} registros - Se muestran primeros 10 </div>


 <div class="table-responsive">
                    <table id="tabla-participantes" class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                              
                                <th>Nombres</th>
                                <th>Documento</th>
                                <th>Género</th>
                                <th>Correo</th>
                                <th>Fecha de Nacimiento</th>
                                <th>Talla Camiseta</th>
                            
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($primer as  $p)
                            <tr>
                 
                                
                                <td>{{ $p->nombres }} {{ $p->apellidos }}</td>
                                <td>{{ $p->numero_documento }}</td>
                                <td>{{ ucfirst($p->genero) }}</td>
                                <td>{{ $p->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->fecha_nacimiento)->format('d/m/Y') }}</td>
                                <td>{{ $p->talla }}</td>
                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>


   <form id="formulario-facturacion" method="POST">
        @csrf

 <div class="row">
 
     <div class="col-md-6">
         <h5 class="celeste">Datos de facturación</h5>
      

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Tipo de Documento</label>
                <select name="tipo_documento" class="form-control" required>
                    <option value="C">Cédula</option>
                    <option value="R">RUC</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label>Documento</label>
                <input type="text" name="numero_documento" class="form-control" required>
            </div>
            <div class="form-group col-md-6">
                <label>Nombre / Razon Social</label>
                <input type="text" name="razon_social" class="form-control" id="nombre_fact" >
            </div>
            <div class="form-group col-md-6">
                <label>Empresa</label>
                <input type="text" name="empresa" class="form-control" id="apellido_fact">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label>Email</label>
                <input type="email" name="email" class="form-control" id="email_fact" >
            </div>
            <div class="form-group col-md-4">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" id="celular_fact" >
            </div>
            <div class="form-group col-md-4">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control" id="direccion_fact" >
            </div>
        </div>

        <div class="form-group">
            <label>Nota Adicional</label>
            <textarea name="nota_adicional" class="form-control" rows="2"></textarea>
        </div>  
    
    </div>
		
		    <div class="col-md-6">

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

  
        
    <button type="submit" formaction="{{ route('admin.corporativas.finalizar') }}"
        class="btn btn-danger" id="btn-corporativas">
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        Finalizar Corporativas
    </button>

    <button type="submit" formaction="{{ route('admin.corporativas.gratuitas') }}"
        class="btn btn-primary" id="btn-gratuitas">
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        Finalizar Gratuitas
    </button>

    <button type="submit" formaction="{{ route('admin.corporativas.linkpago') }}"
        class="btn btn-secondary" id="btn-link">
        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        Link de pago
    </button>


		</div>
    </form>
</div>
</div>
@endif


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('formulario-facturacion');
        const buttons = form.querySelectorAll('button[type="submit"]');

        buttons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Evita el envío inmediato

                // Oculta todos los spinners
                buttons.forEach(btn => btn.querySelector('.spinner-border').classList.add('d-none'));

                // Muestra spinner solo en este botón
                this.querySelector('.spinner-border').classList.remove('d-none');

                // Desactiva todos los botones excepto el clickeado
                buttons.forEach(btn => {
                    if (btn !== this) btn.disabled = true;
                });

                // Espera 800ms antes de enviar el formulario
                setTimeout(() => {
                    form.action = this.getAttribute('formaction');
                    form.submit();
                }, 2000);
            });
        });
    });
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
