@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        Parámetros del Sistema
    </div>

 <div class="participant-form border p-3 mb-4 rounded">
                <h5 class="celeste" >Categorías</h5>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Nombre</th></tr></thead>
        <tbody>
            @foreach($categorias as $cat)
            <tr><td>{{ $cat->id }}</td><td>{{ $cat->nombre }}</td></tr>
            @endforeach
        </tbody>
    </table>

   

                <h5 class="celeste" >Formas de pago</h5>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Nombre</th><th>ACTIVO</th></tr></thead>
        <tbody>
            @foreach($formasPago as $fp)
            <tr><td>{{ $fp->id }}</td><td>{{ $fp->metodo_pago }}</td><td>{{ $fp->estado }}</td></tr>
            @endforeach
        </tbody>
    </table>



    

                <h5 class="celeste" >Tipos de Inscripción</h5>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Nombre</th><th>Valor</th></tr></thead>
        <tbody>
            @foreach($tiposInscripcion as $tipo)
            <tr><td>{{ $tipo->id }}</td><td>{{ $tipo->nombre }}</td><td>{{ $tipo->valor }}</td></tr>
            @endforeach
        </tbody>
    </table>


                <h5 class="celeste" >Inventario Total</h5>
    <table class="table table-bordered">
        <thead><tr><th>ID</th><th>Talla</th><th>Total</th><th>Restante</th></tr></thead>
        <tbody>
            @foreach($inventario as $item)
            <tr><td>{{ $item->id }}</td><td>{{ $item->talla }}</td><td>{{ $item->stock_total }}</td><td>{{ $item->stock_restante }}</td></tr>
            @endforeach
        </tbody>
    </table>

@endsection
