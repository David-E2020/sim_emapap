@extends('layouts.print_inventario')

@section('content')
<style type="text/css">
    table.color {
        border-width: 1px;
        border-spacing: 0px;
        border-color: #E8E8E8;
    }

    table.table-reporte {
        border-width: 1px;
        border-spacing: 0px;
        border-style: outset;
        border-color: black;
        border-collapse: collapse;
        background-color: white;
    }

    table.table-reporte th {
        border-width: 1px;
        padding: 1px;
        border-style: inset;
        border-color: #E8E8E8;
        background:#E8E8E8;
        font-size:10px;

    }

    table.table-reporte td {
        border-width: 1px;
        padding: 2px;
        border-style: inset;
        border-color: #E8E8E8;
        font-size:9px;
    }

</style>
<table class="table-reporte align-top">
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Fecha Registro:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->created_at }}</td>

        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Planta:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->planta_origen->nombre ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Origen:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->origen->nombre}}</td>

        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Responsable Origen:</td>
        <td class="text-center" width='20%'>{{$datos['nombre_responsable_origen']}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Destino:</td>
        <td class="text-center" width='20%'>{{$solicitud_registro->destino->nombre}}</td>

        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Responsable Destino:</td>
        <td class="text-center" width='20%'>{{$datos['nombre_responsable_destino'] ?? ''}}</td>
    </tr>
    <tr>
        <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold"><b>Observacion:</b></td>
        <td class="text-center text-base" colspan="4"><b>{{$observacion }}</b></td>
    </tr>
    <!---->
</table>
@if ($solicitud_registro->tipo_solicitud_id == 3)

    <table class="table-reporte align-top">
        <tr>
            <td colspan="7" class="text-center" style="border: medium transparent"><b>INFORMACION PARA LA VENTA</b></td>
        </tr>
        <tr>
            @if ($datos['tipo_venta_id'] == 2)
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Tipo Transporte:</td>
                <td class="text-center" width='20%'>{{ $tipo_transporte }}</td>
            @endif
        </tr>
        @if ($datos['tipo_venta_id'] == 1)
            <tr>
                <?php $datos_asociacion = App\Models\ProductoPrivado\Asociacion::find($datos['asociacion_id']);?>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Asociacion:</td>
                <td class="text-center" width='20%'>{{ $datos['asociacion'] }}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Numero Celular:</td>
                <td class="text-center" width='20%'>{{ $datos_asociacion->numero_celular }}</td>
            </tr>
        @endif
        @if ($datos['tipo_venta_id'] == 2)
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Cliente:</td>
                <td class="text-center" width='20%'>{{ $datos['cli_razon_social'] }}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Cliente Numero Identificacion:</td>
                <td class="text-center" width='20%'>{{ $datos['cliente_numero_identificacion'] }}</td>
            </tr>
        @endif

        @if ($datos['tipo_venta_id'] == 3)
            <tr>
                <?php $datos_asociacion = App\Models\ProductoPrivado\Asociacion::find($datos['asociacion_id']);?>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Tipo Transporte:</td>
                <td class="text-center" width='20%' colspan="3">{{ $tipo_transporte }}</td>

            </tr>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Asociacion:</td>
                <td class="text-center" width='20%'>{{ $datos['asociacion'] }}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Numero Celular:</td>
                <td class="text-center" width='20%'>{{ $datos_asociacion->numero_celular }}</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Cliente:</td>
                <td class="text-center" width='20%'>{{ $datos['cli_razon_social'] }}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Cliente Numero Identificacion:</td>
                <td class="text-center" width='20%'>{{ $datos['cliente_numero_identificacion'] }}</td>
            </tr>
        @endif


        @if ($datos['tipo_venta_id'] == 2 && $datos['tipo_transporte_id'] == 1 || $datos['tipo_venta_id'] == 3 && $datos['tipo_transporte_id'] == 1)
            <?php
//CLIENTE PRODUCTOR
$vehiculo = $datos['vehiculo'] ?? 0;
$conductor = $datos['conductor'] ?? 0;

$datos_vehiculo = App\Models\ProductoPrivado\Vehiculo::find($vehiculo);
$datos_conductor = App\Models\ProductoPrivado\Conductor::find($conductor);
$nombre_cliente = $datos['cli_razon_social'] ?? 0;
$numero_documento_cliente = $datos['cliente_numero_identificacion'] ?? '';
?>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Nombre Conductor:</td>
                <td class="text-center" width='20%'>{{ $datos_conductor->nombre_completo }}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Numero Identificacion Conductor:</td>
                <td class="text-center" width='20%'>{{ $datos_conductor->numero_identificacion }}</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Placa:</td>
                <td class="text-center" width='20%'>{{ $datos_vehiculo->placa ?? '-'}}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Marca:</td>
                <td class="text-center" width='20%'>{{ $datos_vehiculo->marca ?? '-' }}</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Tipo:</td>
                <td class="text-center" width='20%'>{{ $datos_vehiculo->type ?? '-' }}</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>Color:</td>
                <td class="text-center" width='20%'>{{ $datos_vehiculo->color ?? '-' }}</td>
            </tr>
        @endif
    </table>
@endif
@if ($solicitud_registro->tipo_solicitud_id == 3)
    @if ($datos['tipo_venta_id'] == 2 && $datos['tipo_transporte_id'] == 2999)
        <table class="table-info align-top no-padding no-margins border">
            <br>
            <span>
                <h6>DATOS TRANSPORTE LOGISTICA: </h6>
            </span>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>TIPO VEHICULO:</td>
                <td class="text-center" width='20%'>NISSAN</td>

                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>CONDUCTOR:</td>
                <td class="text-center" width='20%'>Pedro Quispe</td>
            </tr>
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>PLACA:</td>
                <td class="text-center" width='20%'>WEQW333</td>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" width='20%'>CELULAR:</td>
                <td class="text-xs uppercase text-xs text-center">6124121</td>
            </tr>
        </table>
    @endif
@endif
<table class="table-reporte align-top">
    <thead>
        <tr>
            <td colspan="7" class="text-center" style="border: medium transparent"><b>DETALLE DE PRODUCTOS</b></td>
        </tr>
        <tr>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">
                Nro.
            </td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">
                Nombre Comercial
            </td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">
                Nombre Producto
            </td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">
                Codigo
            </td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">
                Unidad
            </td>
            <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold">
                Cantidad Solicitada
            </td>
        </tr>
    </thead>
    <tbody>
        <?php
$count = 1;
$total_quantity = 0;
?>
        @foreach ($solicitud_registro->solicitud_detalles as $item)
            <tr class="text-sm">
                <td class="text-center text-xxs uppercase px-5 py-3">{{ $count++ }}</td>
                <td class="text-center text-xxs uppercase px-5 py-3">{{ $item->producto->nombre ?? ''}}</td>
                <td class="text-center text-xxs uppercase px-5 py-3">{{ $item->articulo->nombre_producto }}</td>
                <td class="text-center text-xxs uppercase px-5 py-3">{{ $item->articulo->identificador_mapeo }}</td>
                <td class="text-center text-xxs uppercase px-5 py-3">{{ $item->articulo->unidad_medida->nombre ?? ''}}</td>
                <td class="text-center text-xxs uppercase px-5 py-3">{{ $item->cantidad}}</td>
            </tr>
            <?php
$total_quantity += $item->cantidad;
?>
        @endforeach
            <tr>
                <td class="text-center bg-grey-lightest text-sm text-black uppercase font-bold" colspan="5">Total</td>
                <td class="text-center text-xxs uppercase font-bold px-5 py-3" >{{ number_format((float)$total_quantity, 6, '.', '')}}</td>
            </tr>
    </tbody>
</table>

<table class="table-reporte">
    @php
        $usuario_solicitado = '';
    @endphp
    <tr style="height: 80px;">
        <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 33%" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td class="text-xs" style="vertical-align: bottom; text-align: center; width: 33%" rowspan="2">
            SELLO Y FIRMA:
        </td>
        <td class="text-center">
            {!! QrCode::format('svg')->size(100)->errorCorrection('H')->color(0, 0, 0)->generate( $solicitud_registro->solicitud_id . ' ' . $solicitud_registro->id . ' ' . $solicitud_registro->dataQr . ' ' . $solicitud_registro->origen_id . ' ' . $solicitud_registro->destino_id . ' ' . $solicitud_registro->planta_origen_id . ' ' . $solicitud_registro->planta_destino_id . ' ' . $solicitud_registro->tipo_venta . ' ' . $solicitud_registro->tipo_solicitud_id . ' ' . $solicitud_registro->created_at ) !!}
        </td>
    </tr>
    <tr>
    </tr>
    <tr>
        <td class="text-center text-xs font-bold" style="text-align: center">
            @if($usuario_solicitado)
                {{$usuario_solicitado->name}}<br>
            @endif
            SOLICITADO - PERSONAL EMAPA
        </td>
        <td class="text-center text-xs font-bold" style="text-align: center">
            APROBADO - PERSONAL EMAPA
        </td>
         <td class="text-center text-xs font-bold" style="text-align: center">
            QR DE AUTENTICIDAD
        </td>
    </tr>
</table>
<br>
<table class="saltopagina">
    <tr>
        <td class="text-left text-xxs">
            Fecha Impresión: {{ $date }} <br>
            Usuario:{{ Auth::user()->name }}
        </td>
        <td class="text-left text-xxs"></td>
        <td class="text-right text-xxs"></td>
        <td class="text-right text-xxs">

        </td>
    </tr>
</table>



@php
use Luecano\NumeroALetras\NumeroALetras;
    function usuario($id){
        $usuario=App\User::find($id);
        return $usuario->usr_usuario;
    }
    function numtoletras($xcifra) {
        $xarray = array(0 => "Cero",
            1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
            "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
            "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
            100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS",
        );
            //
        $xcifra = trim($xcifra);
        $xlength = strlen($xcifra);
        $xpos_punto = strpos($xcifra, ".");
        $xaux_int = $xcifra;
        $xdecimales = "00";
        if (!($xpos_punto === false)) {
            if ($xpos_punto == 0) {
                $xcifra = "0" . $xcifra;
                $xpos_punto = strpos($xcifra, ".");
            }
            $xaux_int = substr($xcifra, 0, $xpos_punto); // obtengo el entero de la cifra a covertir
            $xdecimales = substr($xcifra . "00", $xpos_punto + 1, 2); // obtengo los valores decimales
        }

        $XAUX = str_pad($xaux_int, 18, " ", STR_PAD_LEFT); // ajusto la longitud de la cifra, para que sea divisible por centenas de miles (grupos de 6)
        $xcadena = "";
        for ($xz = 0; $xz < 3; $xz++) {
            $xaux = substr($XAUX, $xz * 6, 6);
            $xi = 0;
            $xlimite = 6; // inicializo el contador de centenas xi y establezco el límite a 6 dígitos en la parte entera
            $xexit = true; // bandera para controlar el ciclo del While
            while ($xexit) {
                if ($xi == $xlimite) { // si ya llegó al límite máximo de enteros
                    break; // termina el ciclo
                }

                $x3digitos = ($xlimite - $xi) * -1; // comienzo con los tres primeros digitos de la cifra, comenzando por la izquierda
                $xaux = substr($xaux, $x3digitos, abs($x3digitos)); // obtengo la centena (los tres dígitos)
                for ($xy = 1; $xy < 4; $xy++) {
                    // ciclo para revisar centenas, decenas y unidades, en ese orden
                    switch ($xy) {
                    case 1: // checa las centenas
                        if (substr($xaux, 0, 3) < 100) {
                            // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas

                        } else {
                            $key = (int) substr($xaux, 0, 3);
                            if (TRUE === array_key_exists($key, $xarray)) {
                                // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                                $xseek = $xarray[$key];
                                $xsub = subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                                if (substr($xaux, 0, 3) == 100) {
                                    $xcadena = " " . $xcadena . " CIEN " . $xsub;
                                } else {
                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                }

                                $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                            } else {
                                // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                $key = (int) substr($xaux, 0, 1) * 100;
                                $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                $xcadena = " " . $xcadena . " " . $xseek;
                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 0, 3) < 100)
                        break;
                    case 2: // checa las decenas (con la misma lógica que las centenas)
                        if (substr($xaux, 1, 2) < 10) {

                        } else {
                            $key = (int) substr($xaux, 1, 2);
                            if (TRUE === array_key_exists($key, $xarray)) {
                                $xseek = $xarray[$key];
                                $xsub = subfijo($xaux);
                                if (substr($xaux, 1, 2) == 20) {
                                    $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                                } else {
                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                }

                                $xy = 3;
                            } else {
                                $key = (int) substr($xaux, 1, 1) * 10;
                                $xseek = $xarray[$key];
                                if (20 == substr($xaux, 1, 1) * 10) {
                                    $xcadena = " " . $xcadena . " " . $xseek;
                                } else {
                                    $xcadena = " " . $xcadena . " " . $xseek . " Y ";
                                }

                            } // ENDIF ($xseek)
                        } // ENDIF (substr($xaux, 1, 2) < 10)
                        break;
                    case 3: // checa las unidades
                        if (substr($xaux, 2, 1) < 1) {
                            // si la unidad es cero, ya no hace nada

                        } else {
                            $key = (int) substr($xaux, 2, 1);
                            $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                            $xsub = subfijo($xaux);
                            $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                        } // ENDIF (substr($xaux, 2, 1) < 1)
                        break;
                    } // END SWITCH
                } // END FOR
                $xi = $xi + 3;
            } // ENDDO

            if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
            {
                $xcadena .= " DE";
            }

            if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
            {
                $xcadena .= " DE";
            }

            // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
            if (trim($xaux) != "") {
                switch ($xz) {
                case 0:
                    if (trim(substr($XAUX, $xz * 6, 6)) == "1") {
                        $xcadena .= "UN BILLON ";
                    } else {
                        $xcadena .= " BILLONES ";
                    }

                    break;
                case 1:
                    if (trim(substr($XAUX, $xz * 6, 6)) == "1") {
                        $xcadena .= "UN MILLON ";
                    } else {
                        $xcadena .= " MILLONES ";
                    }

                    break;
                case 2:
                    if ($xcifra < 1) {
                        $xcadena = " $xdecimales/100 Bolivianos.";
                    }
                    if ($xcifra >= 1 && $xcifra < 2) {
                        $xcadena = " $xdecimales/100 Bolivianos. ";
                    }
                    if ($xcifra >= 2) {
                        $xcadena .= "  $xdecimales/100 Bolivianos "; //
                    }
                    break;
                } // endswitch ($xz)
            } // ENDIF (trim($xaux) != "")
            // ------------------      en este caso, para México se usa esta leyenda     ----------------
            $xcadena = str_replace("VEINTI ", "VEINTI", $xcadena); // quito el espacio para el VEINTI, para que quede: VEINTICUATRO, VEINTIUN, VEINTIDOS, etc
            $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
            $xcadena = str_replace("UN UN", "UN", $xcadena); // quito la duplicidad
            $xcadena = str_replace("  ", " ", $xcadena); // quito espacios dobles
            $xcadena = str_replace("BILLON DE MILLONES", "BILLON DE", $xcadena); // corrigo la leyenda
            $xcadena = str_replace("BILLONES DE MILLONES", "BILLONES DE", $xcadena); // corrigo la leyenda
            $xcadena = str_replace("DE UN", "UN", $xcadena); // corrigo la leyenda
        } // ENDFOR ($xz)
        return trim('SON : ' . $xcadena);
    }
@endphp
@endsection
