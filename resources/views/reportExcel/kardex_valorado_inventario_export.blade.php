
<html>
<table>
  <td><img src="images/logoEmapa2.png" width="100" /></td>
  <td colspan="6" style="text-align:center; vertical-align: middle;"><h1></h1></td>
</table>
<table>
     <tr>
       <td width="5" colspan="16" style="text-align:center;"><strong><h6>KARDEX VALORADO {{ $storage }}</h6></strong></td>
    </tr>
    <tr>
       <td width="5" colspan="16" style="text-align:center;"><strong><h6>EMPRESA DE APOYO A LA PRODUCCION DE ALIMENTOS - EMAPA</h6></strong></td>
    </tr>
    <tr>
       <td width="5" colspan="16" style="text-align:center;"><strong><h6>PRODUCTO: {{ $product->nombre }}</h6></strong></td>
    </tr>
</table>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td class="px-15 py text-center  text-xxs">
                Fecha
            </td>
            <td class="px-15 py text-center  text-xxs">
                Tipo
            </td>
            <td class="px-15 py text-center  text-xxs">
                Movimiento
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Programa
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Campania
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Lote
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Entrada
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Salida
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Saldo
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td colspan="7" class="px-15 py text-center text-xxs">

            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cant.
            </td>
            <td colspan="1" class="px-15 py text-center text-xxs">
                Cant.
            </td>
        </tr>
    </thead>
    <tbody>
        @php
            $saldo=0;
            $count=0;
        @endphp
        @foreach ($history as $index => $item)
            <tr class="text-sm">
                 <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{ $index+1 }}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_fecha}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_tipo}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_tipo_detalle}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_programa}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_campania}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_lote}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_entrada}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_salida}}</td>
            <td class="text-center text-xxs uppercase font-bold px-1 py-1">{{$item->o_saldo}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<br>
<table class="table-info w-100">
    <thead class="bg-grey-darker">
        <tr class="font-medium text-white text-sm">
            <td colspan="5" class="px-15 py text-center text-xxs ">
                    Resumen datos Actual
            </td>
        </tr>
        <tr class="font-medium text-white text-sm">
            <td rowspan="1" class="px-15 py text-center text-xxs ">
                Nro.
            </td>
            <td rowspan="1" class="px-15 py text-center  text-xxs">
                Fecha Ingreso/Movimiento
            </td>
            <td colspan="2" class="px-15 py text-center text-xxs">
                Resumen de Saldos
            </td>

        </tr>
        <tr class="font-medium text-white text-sm">
            <td class="px-15 py text-center  text-xxs"></td>
            <td class="px-15 py text-center  text-xxs"></td>
            <td class="px-15 py text-center  text-xxs">Cant.</td>
            <td class="px-15 py text-center  text-xxs">Total </td>
        </tr>
    </thead>
    <tbody>
        @php
         $total_quantity=0;
         $total_cost=0;
         $total_amount=0;
         $count = 1;
        @endphp
        @foreach ($stocks as $index => $stock)
        <tr class="text-sm">
            <td class="text-center text-xxs uppercase font-bold px-5 py-3" >{{ $count++ }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{Carbon\Carbon::parse($stock->created_at, 'UTC')->format('d-m-Y')}}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->cantidad }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->sk_precio_unitario }}</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3">{{ $stock->cantidad * $stock->sk_precio_unitario}}</td>
            @php
              $total_quantity += $stock->cantidad;
              $total_cost += $stock->sk_precio_unitario;
              $total_amount +=  $stock->cantidad * $stock->sk_precio_unitario;
            @endphp
        </tr>
        @endforeach
        <tr class="text-sm">
            <td colspan="2" class="text-center text-xxs uppercase font-bold px-5 py-3 bg-grey-darker text-white" >TOTAL:</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3" >{{ $total_quantity }}</td>
            {{-- <td class="text-center text-xxs uppercase font-bold px-5 py-3" >{{ $total_cost / sizeof($stocks) }}</td> --}}
            <td class="text-center text-xxs uppercase font-bold px-5 py-3" >-</td>
            <td class="text-center text-xxs uppercase font-bold px-5 py-3" >{{ $total_amount }}</td>
        </tr>
    </tbody>
</table>

</html>