@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
       FACTURACIÓN
    </div>
 <div class="p-3  mb-4 rounded">
 <h5 class="celeste">Inscripciones Gratuitas</h5>

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
                                <th>Nacionalidad</th>
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
                                <td>{{ $p->nacionalidad }}</td>
                                <td>{{ \Carbon\Carbon::parse($p->fecha_nacimiento)->format('d/m/Y') }}</td>  
                                <td>{{ $p->talla_camiseta }}</td>
                               
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

    <form action="{{ route('admin.inscripciones_gratuitas.finalizar') }}" method="POST"> 
        @csrf

 <div class="row">
 
     
		
		    <div class="col-md-5">

     <h5 class="celeste">Resumen de Pago</h5>
        <table class="table ">
            <tr><th>SubTotal</th><td>${{ number_format($subtotal, 2) }}</td></tr>
            <tr><th>IVA</th><td>${{ number_format($iva_total, 2) }}</td></tr>
            <tr><th>Total</th><td><strong>${{ number_format($total, 2) }}</strong></td></tr>
        </table>


 

  
        

        <button type="submit" class="btn btn-primary">Finalizar Inscripción</button>
		</div>
    </form>
</div>
</div>
@endif



@endsection
