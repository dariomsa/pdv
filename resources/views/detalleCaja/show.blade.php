@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
		<tr>
			<td> 	
				Informacion de Cierre de Caja
			</td>
		</tr>
    </div>	
    <div class="card-body">
        <div class="mb-2">
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
						<th COLSPAN=2>
									   CIERRE DE CAJA
						</th>
					</tr>
                    <tr>
                        <th>
                            ID
                        </th>
                        <td>
                            {{ $caja->id }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Punto de Venta
                        </th>
                        <td>
                            {{ $puntoventa->name }}
                        </td>
					</tr>						
                    <tr>
                        <th>
                            Fecha
                        </th>
                        <td>
                            {{ $caja->fecha }}
                        </td>
					</tr>	
                    <tr>
                        <th>
                            Secuencia
                        </th>
                        <td>
                            {{ $caja->secuencia }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            N&uacutemero de Facturas
                        </th>
                        <td>
                            {{ $caja->numero_facturas }}
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Monto Total USD
                        </th>
                        <td>
                            {{ $caja->monto_total }}
                        </td>
                    </tr>
			
                </tbody>
            </table>
			<table class="table table-bordered table-striped">
                <thead>
                    <tr>
						<th COLSPAN=3>
									   DESGLOSE CIERRE DE CAJA
						</th>
					</tr>
					<tr>
						<th>Forma de Pago</th>
						<th># Inscripciones</th>
						<th>Monto</th>
					</tr>					
				</thead>
				<tbody>
					<?php 
						if(isset($detalle)){
							foreach($detalle as $det){
								$nomPago = App\FormasPago::select('metodo_pago')->where('id', $det->id_forma_pago)->first();
								?>
								<tr>
									<td>{{$nomPago->metodo_pago}}</td>
									<td style="text-align:right;">{{$det->numero_facturas}}</td>
									<td>{{$det->monto}}</td>
								</tr>					
					
						<?php } ?>
					<?php } ?>

				</tbody>				
			</table>
            <a style="margin-top:20px;" class="btn btn-default" href="{{ url()->previous() }}">
                {{ trans('global.back_to_list') }}
            </a>
        </div>   					
</div>
@endsection