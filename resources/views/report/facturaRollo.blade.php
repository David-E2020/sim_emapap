@inject('reporteFactura', 'App\Http\Controllers\Reporte\ReporteFacturaController')
<html>
    <head>


        <meta charset="UTF-8">
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <link rel="shortcut icon" href="../../images/user1.png">

        <link rel="stylesheet" type="text/css" href="CSS.css">
        <title>Factura Termica</title>
        <style type="text/css" media="print">
            @page {size: portrait;}
            @page rotada{size: landscape;}
            @page  {margin: 0;}
        </style>
        <style type="text/css">
            body {

                width: 200px;
                /* font-size: large;*/
            }

            div {
                width: 250px;
                /*   font-size: large;*/
            }

            tfoot {
                padding-top: 5px;
            }

            .css-content_center {
                text-align: center;
            }

            .css-header_table {
                ¡ border: 1px solid #000;
                */ border-top: 1px solid;
                border-bottom: 1px solid;
            }

            .css-footer_table {
                /border: 1px solid #000;/
                border-top: 1px solid;
            }

            .css-info_table {
                width: 89%;
            }

            .css-info_table td {
                padding-right: 2px;
                padding-left: 2px;
            }

            .css-info_table1 {
                width: 100%;

            }
   .css-info_table11 {
                width: 100%;

            }
             .css-info_table11 td {
                padding-right: 0px;
                padding-left: 7px;
            }
            .css-info_table1 td {
                padding-right: 0px;
                padding-left: 7px;
            }

            .css-info_table2 {
                width: 100%;
            }

            .css-info_table2 td {
                padding-right: 10px;
                padding-left: 7px;
            }

            .css-info_table3 {
                width: 100%;
            }

            .css-info_table3 td {
                padding-right: 10px;
                padding-left: 7px;
            }

        </style>

        <link href="../../plugins/bootstrap/css/bootstrap.css" rel="stylesheet">


        <script type="text/javascript">
            function imprimir() {
                if (window.print) {
                    window.print();
                } else {
                    alert("La función de impresion no esta soportada por su navegador.");
                }
            }
        </script>
    </head>

    <body onload="imprimir();">
    <!--<div class="css-content_center">
        <img src="../../images/user1.png" alt="SEDEM" height="50" width="125">
    </div>-->

    <div id="muestra">
    <div class="css-content_center">
        <h5 class="css-content_center">
            
FACTURA COMERCIAL DE EXPORTACIÓN <br>
(COMMERCIAL INVOICE) <br>
SIN DERECHO A CRÉDITO FISCAL <br>

        </h5>
    </div>

        <div class="css-content_center">
            <FONT SIZE=1>
                EMPRESA BOLIVIANA DE ALIMENTOS Y <br>
DERIBADOS - EBA

            </FONT>
        </div>
        <div class="css-content_center">
            <FONT SIZE=1>Casa Matriz</FONT>
        </div>
        <div class="css-content_center">
            <FONT SIZE=1>
                No. Punto de Venta 0

            </FONT>
        </div>
        <div class="css-content_center">
            <FONT SIZE=1>
                Avenida Arce Nro 2382, Edificio Hermanos Maldonado
            </FONT>
        </div>
       


        <div class="css-content_center">
            <FONT SIZE=1>Cel. 2145697-65151877</FONT>

        </div>
        <div class="css-content_center">
            <FONT SIZE=1>La Paz</FONT>
        </div>

    <div class="css-content_center">
        <FONT SIZE=1>---------------------------------------------------------------------------</FONT>
    </div>
    <div class="css-content_center">
        <table class="css-info_table">
            <tbody>
            <tr>
                <td align="right" style="font-weight: bold;" ><FONT SIZE=1>NIT: </FONT></td>
                <td align="left"><FONT SIZE=1>368406024</FONT></td>
            </tr>
            <tr>
                <td align="right" style="font-weight: bold;" ><FONT SIZE=1>FACTURA N°.: </FONT></td>
                <td align="left"><FONT SIZE=1>{{$factura->id}}</FONT></td>
            </tr>
            <tr>
                <td align="right" style="font-weight: bold;" ><FONT SIZE=1>CÓD. AUTORIZACIÓN.: </FONT></td>
                <td align="left"><FONT SIZE=1>{!! $cuf_preview !!} </FONT></td>
            </tr>
            </tbody>
        </table>

    </div>
    <div class="css-content_center">
        <!--  <FONT SIZE=1>- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - </FONT>-->
        <!--  <FONT SIZE=1>- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - </FONT>-->
        <FONT SIZE=1>---------------------------------------------------------------------------</FONT>
    </div>


  
    <div class="css-content_center">
        <table class="css-info_table11">
            <tbody>
            <tr>
                <td align="right" style="font-weight: bold;" ><FONT SIZE=1>NOMBRE/RAZÓN SOCIAL: </FONT>

                </td>

                <td align="left" ><FONT SIZE=1>{{$cabecera->nombreRazonSocial}}</FONT></td>
            </tr>
           
            <tr>
                <td align="right" style="font-weight: bold;" width="65"><FONT SIZE=1>NIT/CI/CEX: </FONT></td>
                <td align="left"><FONT SIZE=1>{{$cabecera->codigoCliente}}</FONT></td>
            </tr>


            <tr>
                <td align="right" width="65" style="font-weight: bold;" ><FONT SIZE=1>FECHA DE EMISIÓN: </FONT></td>
                <td align="left"><FONT SIZE=1>
                    {{$fechaEmision}}
                </FONT></td>
            </tr>



            <tr>
                <td align="right" width="65" style="font-weight: bold;" ><FONT SIZE=1>INCOTERM: </FONT></td>
                <td align="left"><FONT SIZE=1>
                    {{$cabecera->incoterm}}
                </FONT></td>
            </tr>

            <tr>
                <td align="right" width="65" style="font-weight: bold;" ><FONT SIZE=1>DIRECCIÓN COMPRADOR: </FONT></td>
                <td align="left"><FONT SIZE=1>
                    {{$cabecera->direccionComprador}}
                </FONT></td>
            </tr>

            <tr>
                <td align="right" width="65" style="font-weight: bold;" ><FONT SIZE=1>PUERTO DESTINO: </FONT></td>
                <td align="left"><FONT SIZE=1>
                    {{$cabecera->lugarDestino}}
                </FONT></td>
            </tr>

            <tr>
                <td align="right" width="65" style="font-weight: bold;" ><FONT SIZE=1>MONEDA TRANSACCCIÓN: </FONT></td>
                <td align="left"><FONT SIZE=1>
                    {{$codigoMonedaNombre->param_nombre}}
                </FONT></td>
            </tr>

            <tr>
                <td align="right" width="65" style="font-weight: bold;" ><FONT SIZE=1>TIPO DE CAMBIO: </FONT></td>
                <td align="left"><FONT SIZE=1>
                    {{$cabecera->tipoCambio}}
                </FONT></td>
            </tr>

            

            </tbody>
        </table>
    </div>

    <div class="css-content_center">
        <FONT SIZE=1>---------------------------------------------------------------------------</FONT>
    </div>




<div class="css-content_center">
    <FONT SIZE=1>DETALLE</FONT>

    <table class="css-info_table1">
        <tbody class="table">
             <?php $pos=1 ?>
            @foreach($detalles as $det)
            <tr>
                <td style="font-weight: bold;" > <FONT SIZE=1><?php print $pos++ ?> - {{ $det->descripcion}}</FONT> </td>
                <td></td>
            </tr>

            <tr>
                <td><FONT SIZE=1>{{ $det->cantidad}} X {{ $det->precioUnitario}}</FONT> </td>
                <td style="font-weight: bold;" ><FONT SIZE=1>{{ $det->subTotal}}</FONT> </td>
            </tr>
            @endforeach

           
        </tbody>
        
    </table>

     <div class="css-content_center">
        <FONT SIZE=1>..............................................................................</FONT>
    </div>

    <table class="css-info_table1"  >
        <tbody class="table">
            <tr  >
                <td style="font-weight:bold;" ><FONT SIZE=1>TOTAL DETALLE ({{$codigoMonedaNombre->param_nombre}}) </FONT></td>
                <td style="font-weight:bold;" > <FONT SIZE=1>{{$cabecera->montoDetalle}}</FONT></td>
            </tr>
        </tbody>
    </table>



    
    
</div>

 <div class="css-content_center">
        <FONT SIZE=1>---------------------------------------------------------------------------</FONT>
    </div>

<div class="css-content_center">
   <div align="left" style="font-weight:bold;" ><FONT SIZE=1>Desglose de Costos y Gastos Nacionales </FONT></div>  
   <div align="left" ><FONT  SIZE=1>(National Costs and Expenses Detail)</FONT></div>
   

 <table class="css-info_table1" style="border-collapse: collapse !important;" >
        <tbody class="table" >
            @foreach(json_decode($cabecera->costosGastosNacionales, true) as $key => $value)
            <tr style="border: 1px solid ; " >
                <td ><FONT SIZE=1>{{ $key }} </FONT></td>
                <td> <FONT SIZE=1>{{ $value }}</FONT></td>
            </tr>
            @endforeach
            <tr>
                <td><FONT SIZE=1>SubTotal FOB (Frontera)</FONT></td>
                <td><FONT SIZE=1>{{$cabecera->totalGastosNacionalesFob}}</FONT></td>
            </tr>
        </tbody>
    </table>

<br>
    <div align="left" style="font-weight:bold;" ><FONT SIZE=1>Desglose de Costos y Gastos Internacionales </FONT></div>  
   <div align="left" ><FONT  SIZE=1>(International Costs and Expenses Detail)</FONT></div>


    <table class="css-info_table1" style="border-collapse: collapse !important;" >
        <tbody class="table" >
            @foreach(json_decode($cabecera->costosGastosInternacionales, true) as $key => $value)
            <tr style="border: 1px solid ; " >
                <td ><FONT SIZE=1>{{ $key }} </FONT></td>
                <td> <FONT SIZE=1>{{ $value }}</FONT></td>
            </tr>
            @endforeach
            
        </tbody>
    </table>



        <table class="css-info_table1"  >
        <tbody class="table">
            <tr>
                <td  ><FONT SIZE=1>SUBTOTAL (DOLAR) </FONT></td>
                <td  align="right" > <FONT SIZE=1>{{$cabecera->montoTotalMoneda}}</FONT></td>
            </tr>

            <tr>
                <td  ><FONT SIZE=1>DESCUENTO (DOLAR) </FONT></td>
                <td align="right" > <FONT SIZE=1>{{$cabecera->descuentoAdicional}}</FONT></td> 
            </tr>

            <tr>
                <td style="font-weight:bold;" ><FONT SIZE=1>TOTAL GENERAL (DOLAR) </FONT></td>
                <td style="font-weight:bold; " align="right" > <FONT SIZE=1>{{$cabecera->montoTotalMoneda}}</FONT></td> 
            </tr>

            <tr>
                <td style="font-weight:bold;" ><FONT SIZE=1>TOTAL GENERAL </FONT></td>
                <td style="font-weight:bold; " align="right" > <FONT SIZE=1>{{$cabecera->montoTotal}}</FONT></td>
            </tr>

            <tr>
                <td colspan="2" >
                   <FONT SIZE=1> Son: {{$reporteFactura::numtoletras($cabecera->montoTotalMoneda, 'DOLAR')}} </FONT>
                </td>
                
                </tr>
                <tr>
                    <td colspan="2" >
                  <FONT SIZE=1>  Son: {{$reporteFactura::numtoletras($cabecera->montoTotal, 'Bolivianos')}} </FONT>
                </td>
                </tr>
        </tbody>
    </table>


</div>
    
 <div class="css-content_center">
        <FONT SIZE=1>---------------------------------------------------------------------------</FONT>
    </div>

    <div class="css-content_center">
       
        

        <div class="css-content_center">
            <table class="css-info_table2">
                <tbody>
               
                <tr>
                    <td align="center">
                        
                        {!!QrCode::size(150)->generate($urlQR) !!}
                    </td>
                </tr>
                <tr>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="css-content_center">

                        <FONT SIZE=1><i>"ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS,
EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE
ACUERDO A LEY"</i></FONT><br>
                        <FONT SIZE=1>
                            
                            {{$cabecera->leyenda}}
                        </FONT>

        </div>
        <div class="css-content_center">
            <FONT SIZE=1>---------------------------------------------------------------------------</FONT>
        </div>
        <div class="css-content_center">
            <table class="css-info_table11">
                <tbody>
                <tr>
                    <td align="left"><FONT SIZE=1>Usuario : </FONT></td>

                    <td align="left"><FONT SIZE=1>{{ $username }}</FONT></td>
                </tr>
                <tr>
                    <td align="left"><FONT SIZE=1>Fecha Impresion: </FONT></td>
                    
                    <td align="left"><FONT SIZE=1>{{$dateServe}}</FONT></td>
                </tr>
                </tbody>
            </table>
        </div>
        <br>
        <div class="css-content_center">
            <FONT SIZE=1>GRACIAS POR SU PREFERENCIA</FONT>
        </div>
    </div>

    </body>
   
    </html>