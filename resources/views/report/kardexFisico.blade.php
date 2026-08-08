@extends('layouts.printKardex')

@section('content')

<br>
<table class="table-info align-top no-padding no-margins border w-100">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white w-20">Código Producto:</td>
        <td class="text-xs uppercase w-80 ">{{$existencia->producto->prod_codigo}}</td>
        
	</tr>
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white w-20 ">Unidad:</td>
        <td class="text-xs uppercase w-80 ">{{$existencia->sk_presentacion}}</td>
    
    </tr>

</table>


<br>


<table class="table-info w-100">
	<thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm"> 
            <th class="px-15 py text-center text-xxs " >Nro.</th>
            <th class="px-15 py text-center text-xxs " >Fecha</th>
            <th class="px-15 py text-center text-xxs " >Tipo</th>
            <th class="px-15 py text-center text-xxs " >Detalle</th>
            <th class="px-15 py text-center text-xxs " >Entrada</th>
            <th class="px-15 py text-center text-xxs " >Salida</th>
            <th class="px-15 py text-center text-xxs " >Saldo</th>
        </tr>
       
	</thead>
	<tbody>


			@php
	            $totalSaldo =null;
				$cantidadPresente=null;
				$salida=0;
				$total_=0;
			@endphp

		@foreach($movDetalle as $det)

			@php
				$ingreso = ($det->movimiento->mv_tipo=='INGRESO')?$det->mvd_cantidad_present:0;
				$salida	= ($det->movimiento->mv_tipo=='SALIDA')?$det->mvd_cantidad_present:0;
 			@endphp
  
			<tr class="text-sm">
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">fgdfg</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$det->fecha}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{($det->movimiento->mv_tipo?? '-')}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{($det->movimiento->mv_tipo?? '-')}} (N{{$det->movimiento->mv_tipo=='INGRESO'?'I':'S'}}° {{($det->movimiento->mv_nro?? '-')}})
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{($det->movimiento->mv_tipo=='INGRESO'?number_format($det->mvd_cantidad_present, 2, '.', ','):'')}}
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{($det->movimiento->mv_tipo=='SALIDA'?number_format($det->mvd_cantidad_present, 2, '.', ','):'')}}
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
			@php
				if($ingreso==0){
					$total_  = $total_ - (int)$det->mvd_cantidad_present;
				} else {
					$total_ =$ingreso;
				}
			@endphp
			   {{(int)$total_}}
				</td>
			</tr>
		@endforeach




	</tbody>
</table>
<br>
<br>

<br>
<table class="w-100">
	<tr>
        <td class="w-50">Revisado por firma:...........................</td>
        <td class="w-50 ">Verificado por firma:...........................</td>
	</tr>


</table>

<br>
<table class="w-100">
	

	<tr>
        <td class="w-50">Nombre:............................................</td>
        <td class="w-50">Nombre:.............................................</td>
	</tr>
</table>


@endsection