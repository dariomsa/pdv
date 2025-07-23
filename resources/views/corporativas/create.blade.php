@extends('layouts.admin')
@section('content')
   <div class="card">

    <div class="card-header">
       CREAR INSCRIPCIONES CORPORATIVAS
    </div>


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
    

		<div class="card-body">
   <form action="{{ route('admin.corporativas.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
          

            <div class="col-md-6">
               
                <label for="archivo_excel">Archivo Excel de Inscritos</label>
                <input type="file" name="archivo_excel" class="form-control" accept=".xlsx,.xls" required>
            </div>
        </div>

        <br>
        <button type="submit" class="btn btn-success">Siguiente</button>
      
    </form>
</div>
</div>
@endsection
