@extends('layouts.printKardex')

@section('content')

<br>
<table class="table-info align-top no-padding no-margins border w-100">
    <tr>
        <td class="text-center uppercase bg-grey-darker text-xs text-white w-20">Código Producto:</td>
        <td class="text-xs uppercase w-80 ">{{$producto->prod_codigo}}</td>
        
	</tr>
    <tr>
        <td class="text-center uppercase bg-grey-darker text-xs text-white w-20 ">Detalle:</td>
        <td class="text-xs uppercase w-80 "> {{$producto->prod_desc}} / {{$producto->tipo->param_nombre}} / {{$producto->calidad->param_nombre}} </td>
    
    </tr>

</table>







<br>


@if(count($entradasSalidasCaja)!=0)
<table class="table-info w-100">
	<thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm"> 
            <th class="px-15 py text-center text-xxs " >NRO.</th>
            <th class="px-15 py text-center text-xxs " >FECHA</th>
            <th class="px-15 py text-center text-xxs " >TIPO</th>
            <th class="px-15 py text-center text-xxs " >PRESENTACIÓN</th>
            <th class="px-15 py text-center text-xxs " >DETALLE</th>
            <th class="px-15 py text-center text-xxs " >ENTRADA</th>
            <th class="px-15 py text-center text-xxs " >DETALLE ENTRADA</th>
            <th class="px-15 py text-center text-xxs " >SALIDA</th>
            <th class="px-15 py text-center text-xxs " >DETALLE SALIDA</th>
            <th class="px-15 py text-center text-xxs " >SALDO CANTIDAD</th>
            <th class="px-15 py text-center text-xxs " >LIBRA</th>
        </tr>
       
	</thead>
	<tbody>
			@php
				$nro=1;
				$resultCantidad=0;
				$resultMedida=0;
			@endphp

		@foreach($entradasSalidasCaja as $det)


	
			@php

				if($det->movimiento->mv_tipo=='INGRESO'){
				$resultCantidad= $resultCantidad + $det->mvd_cantidad_present;
				$resultMedida= $resultMedida + $det->mvd_cantidad_medida;
				}

				if($det->movimiento->mv_tipo=='SALIDA'){
					$resultCantidad= $resultCantidad - $det->mvd_cantidad_present;
					$resultMedida= $resultMedida + $det->mvd_cantidad_medida;
				}

			@endphp

			
			<tr class="text-sm">
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">  <?php print $nro++ ?> </td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$det->fecha}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{($det->movimiento->mv_tipo)}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{($det->mvd_presentacion)}}
					<!--- <br>
					         Lote :  {{($det->mvd_nro_lote)}}    -->                                                                                                                                                                  
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{($det->movimiento->mv_tipo?? '-')}} <br> (N{{$det->movimiento->mv_tipo=='INGRESO'?'I':'S'}}° {{($det->movimiento->mv_nro?? '-')}})
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					
					@if($det->movimiento->mv_tipo=='INGRESO')
						 {{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 
					@else
					-
         			@endif
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1" >
					@if($det->movimiento->mv_tipo=='INGRESO')
       				{{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 	 {{$det->mvd_presentacion}}(S) DE {{number_format($det->mvd_presentacion_medida, 0, '.', ',')}}   Libras
       				@else
       				-
         			@endif
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					@if($det->movimiento->mv_tipo=='SALIDA')
       					{{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 
       				@else
       				-
         			@endif
				</td>

				<td class="text-center text-xxs uppercase font-bold px-1 py-1">

					@if($det->movimiento->mv_tipo=='SALIDA')
     					 {{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 	 {{$det->mvd_presentacion}}(S) DE {{number_format($det->mvd_presentacion_medida, 0, '.', ',')}}   Libras
     				@else
     				-
         			@endif
         			
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{$resultCantidad}}
				</td>

				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{$resultMedida}}
				</td>
			</tr>
			
		@endforeach

	


	</tbody>
</table>
@endif

@if(count($entradasSalidasPaquete)!=0)
<br>

<!-------------------------- PAQUETE --------------------------------------->

   <table class="table-info w-100">
	<thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm"> 
            <th class="px-15 py text-center text-xxs " >NRO.</th>
            <th class="px-15 py text-center text-xxs " >FECHA</th>
            <th class="px-15 py text-center text-xxs " >TIPO</th>
            <th class="px-15 py text-center text-xxs " >PRESENTACIÓN</th>
            <th class="px-15 py text-center text-xxs " >DETALLE</th>
            <th class="px-15 py text-center text-xxs " >ENTRADA</th>
            <th class="px-15 py text-center text-xxs " >DETALLE ENTRADA</th>
            <th class="px-15 py text-center text-xxs " >SALIDA</th>
            <th class="px-15 py text-center text-xxs " >DETALLE SALIDA</th>
            <th class="px-15 py text-center text-xxs " >SALDO CANTIDAD</th>
            <th class="px-15 py text-center text-xxs " >LIBRA</th>
        </tr>
       
	</thead>
	<tbody>
			@php
				$nro=1;
				$resultCantidad=0;
				$resultMedida=0;
			@endphp

		@foreach($entradasSalidasPaquete as $det)

			@php

				if($det->movimiento->mv_tipo=='INGRESO'){
				$resultCantidad= $resultCantidad + $det->mvd_cantidad_present;
				$resultMedida= $resultMedida + $det->mvd_cantidad_medida;
				}

				if($det->movimiento->mv_tipo=='SALIDA'){
					$resultCantidad= $resultCantidad - $det->mvd_cantidad_present;
					$resultMedida= $resultMedida + $det->mvd_cantidad_medida;
				}

			@endphp

			
			<tr class="text-sm">
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">  <?php print $nro++ ?> </td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$det->fecha}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{($det->movimiento->mv_tipo)}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{($det->mvd_presentacion)}}
					<!--- <br>
					         Lote :  {{($det->mvd_nro_lote)}}    -->                                                                                                                                                                  
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{($det->movimiento->mv_tipo?? '-')}} <br> (N{{$det->movimiento->mv_tipo=='INGRESO'?'I':'S'}}° {{($det->movimiento->mv_nro?? '-')}})
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					
					@if($det->movimiento->mv_tipo=='INGRESO')
						 {{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 
         			@else
     				-
         			@endif
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1" >
					@if($det->movimiento->mv_tipo=='INGRESO')
       				{{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 	 {{$det->mvd_presentacion}}(S) DE {{number_format($det->mvd_presentacion_medida, 0, '.', ',')}}   Libras
         			@else
     				-
         			@endif
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					@if($det->movimiento->mv_tipo=='SALIDA')
       					{{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 
         			@else
     				-
         			@endif
				</td>

				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					@if($det->movimiento->mv_tipo=='SALIDA')
     					 {{number_format($det->mvd_cantidad_present, 0, '.', ',')}} 	 {{$det->mvd_presentacion}}(S) DE {{number_format($det->mvd_presentacion_medida, 0, '.', ',')}}   Libras
         			@else
     				-
         			@endif
				</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{$resultCantidad}}
				</td>

				<td class="text-center text-xxs uppercase font-bold px-1 py-1">
					{{$resultMedida}}
				</td>
			</tr>
			
		@endforeach

	


	</tbody>
</table> 
@endif

   

 




<br>

<span class="text-xs uppercase w-80" >RESUMEN SALDO</span>

	@php

	//"sk_presentacion_medida": "600.00000",
	//"sk_cantidad_present": "5.00000",
	//"sk_cantidad_medida": "3000.00000",

	$totalResentacionMedidaCaja=0;
	$totalCantidadPresentCaja=0;
	$totalCantidadMedidaCaja=0;

	$totalResentacionMedidaPaquete=0;
	$totalCantidadPresentPaquete=0;
	$totalCantidadMedidaPaquete=0;

    foreach($producto->existencias as $key => $value) {


		if($value->sk_presentacion=='CAJA'){
			$totalResentacionMedidaCaja = $totalResentacionMedidaCaja + $value->sk_presentacion_medida;
			$totalCantidadPresentCaja = $totalCantidadPresentCaja + $value->sk_cantidad_present;
			$totalCantidadMedidaCaja = $totalCantidadMedidaCaja + $value->sk_cantidad_medida;

		}
		if($value->sk_presentacion=='PAQUETE'){
			$totalResentacionMedidaPaquete = $totalResentacionMedidaPaquete + $value->sk_presentacion_medida;
			$totalCantidadPresentPaquete = $totalCantidadPresentPaquete + $value->sk_cantidad_present;
			$totalCantidadMedidaPaquete = $totalCantidadMedidaPaquete + $value->sk_cantidad_medida;

		}

		
    }
				
	@endphp


<table class="w-100" >
    <tr>
        <td class="w-60"></td>
        <td class="w-40" >
        	<table class="table-info w-100">
	<thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm"> 
            <th class="px-15 py text-left uppercase text-xxs " >Presentación</th>
            <th class="px-15 py text-center  uppercase text-xxs " >Cantidad</th>
            <th class="px-15 py text-center uppercase text-xxs " >Total (Libra)</th>
        </tr>
	</thead>





	<tbody>
			<tr class="text-sm">
				<td class="text-left text-xxs uppercase font-bold px-1 py-1">CAJA </td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$totalCantidadPresentCaja}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$totalCantidadMedidaCaja}}</td>
			</tr>

			<tr class="text-sm">
				<td class="text-left text-xxs uppercase font-bold px-1 py-1">PAQUETE </td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$totalCantidadPresentPaquete}}</td>
				<td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$totalCantidadMedidaPaquete}}</td>
			</tr>
		

	</tbody>
</table>
        </td>
        
	</tr>
</table>



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