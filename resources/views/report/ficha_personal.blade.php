@extends('layouts.print')

@section('content')

<table class="table-info align-top no-padding no-margins border">
    <tr>
        <td class="text-center bg-grey-darker text-xs text-white ">Responsable de Almacén:</td>
        <td colspan="7" class="text-xs uppercase"></td>
		
	</tr>
	<tr>
       
		<td class="text-center bg-grey-darker text-xs text-white ">Dependencia:</td>
        <td class="text-xs uppercase"></td>
		<td  class="text-center bg-grey-darker text-xs text-white">Nro. Factura:</td>
        <td  colspan="3" class="text-xs uppercase"></td>
	</tr>
	<tr>
		
        <td class="text-center bg-grey-darker text-xs text-white ">Tipo Ingreso:</td>
        <td class="text-xs uppercase"></td>
        <td class="text-center bg-grey-darker text-xs text-white ">No. Contrato:</td>
        <td class="text-xs uppercase"></td>
		
	</tr>
    <tr>
        <td  class="text-center bg-grey-darker text-xs text-white">Fecha Nota Rem:</td>
        <td  class="text-xs uppercase"></td>
        <td  class="text-center bg-grey-darker text-xs text-white">Nota Remision:</td>
        <td  class="text-xs uppercase"></td>
      
    </tr>
	<tr>
		
        <td class="text-center bg-grey-darker text-xs text-white ">Proveedor:</td>
        <td class="text-xs uppercase"></td>
        <td class="text-center bg-grey-darker text-xs text-white ">Telefono Proveedor:</td>
        <td class="text-xs uppercase"></td>
		
	</tr>

</table>

<br>

<table class="table-info w-100">
	<thead class="bg-grey-darker">
		<tr class="font-medium text-white text-sm">
			<td class="px-15 py text-center text-xxs ">
				Nro.
			</td>
			<td class="px-15 py text-center  text-xxs">
				Codigo
			</td>
			<td class="px-15 py text-center text-xxs">
				Descripcion
			</td>
			<td class="px-15 py text-center  text-xxs">
				Unidad
			</td>
			<td class="px-15 py text-center text-xxs">
				Fecha venc.
			</td>
			<td class="px-15 py text-center text-xxs">
				Lote
			</td>
			<td class="px-15 py text-center text-xxs">
				Cantidad
			</td>
			<td class="px-15 py text-center text-xxs">
				Costo U.
			</td>
			<td class="px-15 py text-center text-xxs">
				Costo Tot.
			</td>
		</tr>
	</thead>
	<tbody>
			
	</tbody>
</table>
@endsection

