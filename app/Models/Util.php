<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Session;
use SoapFault;
use Exception;
use Carbon\Carbon;
use DateTime;

class Util extends Model
{


    public static function numberFormatViewV2($value) {
        return number_format((float)$value, 2, '.', ',');
       }

public static function numberFormatFloat($value) {
 return number_format((float)$value, 5, '.', '');
}

public static function numberFormatView($value) {
 return number_format((float)$value, 5, '.', ',');
}

public static function numberFormatViewCantidad($value) {
 return number_format((float)$value, 0, '.', ',');
}

public static function numtoletras($xcifra, $moneda="Bolivianos") {

	            $xarray = array(0 => "Cero",
                1 => "UN", "DOS", "TRES", "CUATRO", "CINCO", "SEIS", "SIETE", "OCHO", "NUEVE",
                "DIEZ", "ONCE", "DOCE", "TRECE", "CATORCE", "QUINCE", "DIECISEIS", "DIECISIETE", "DIECIOCHO", "DIECINUEVE",
                "VEINTI", 30 => "TREINTA", 40 => "CUARENTA", 50 => "CINCUENTA", 60 => "SESENTA", 70 => "SETENTA", 80 => "OCHENTA", 90 => "NOVENTA",
                100 => "CIENTO", 200 => "DOSCIENTOS", 300 => "TRESCIENTOS", 400 => "CUATROCIENTOS", 500 => "QUINIENTOS", 600 => "SEISCIENTOS", 700 => "SETECIENTOS", 800 => "OCHOCIENTOS", 900 => "NOVECIENTOS"
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
                    for ($xy = 1; $xy < 4; $xy++) { // ciclo para revisar centenas, decenas y unidades, en ese orden
                        switch ($xy) {
                            case 1: // checa las centenas
                                if (substr($xaux, 0, 3) < 100) { // si el grupo de tres dígitos es menor a una centena ( < 99) no hace nada y pasa a revisar las decenas

                                } else {
                                    $key = (int)substr($xaux, 0, 3);
                                    if (TRUE === array_key_exists($key, $xarray)) {  // busco si la centena es número redondo (100, 200, 300, 400, etc..)
                                        $xseek = $xarray[$key];
                                        $xsub = Util::subfijo($xaux); // devuelve el subfijo correspondiente (Millón, Millones, Mil o nada)
                                        if (substr($xaux, 0, 3) == 100)
                                            $xcadena = " " . $xcadena . " CIEN " . $xsub;
                                        else
                                            $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                        $xy = 3; // la centena fue redonda, entonces termino el ciclo del for y ya no reviso decenas ni unidades
                                    } else { // entra aquí si la centena no fue numero redondo (101, 253, 120, 980, etc.)
                                        $key = (int)substr($xaux, 0, 1) * 100;
                                        $xseek = $xarray[$key]; // toma el primer caracter de la centena y lo multiplica por cien y lo busca en el arreglo (para que busque 100,200,300, etc)
                                        $xcadena = " " . $xcadena . " " . $xseek;
                                    } // ENDIF ($xseek)
                                } // ENDIF (substr($xaux, 0, 3) < 100)
                                break;
                            case 2: // checa las decenas (con la misma lógica que las centenas)
                                if (substr($xaux, 1, 2) < 10) {

                                } else {
                                    $key = (int)substr($xaux, 1, 2);
                                    if (TRUE === array_key_exists($key, $xarray)) {
                                        $xseek = $xarray[$key];
                                        $xsub = Util::subfijo($xaux);
                                        if (substr($xaux, 1, 2) == 20)
                                            $xcadena = " " . $xcadena . " VEINTE " . $xsub;
                                        else
                                            $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                        $xy = 3;
                                    } else {
                                        $key = (int)substr($xaux, 1, 1) * 10;
                                        $xseek = $xarray[$key];
                                        if (20 == substr($xaux, 1, 1) * 10)
                                            $xcadena = " " . $xcadena . " " . $xseek;
                                        else
                                            $xcadena = " " . $xcadena . " " . $xseek . " Y ";
                                    } // ENDIF ($xseek)
                                } // ENDIF (substr($xaux, 1, 2) < 10)
                                break;
                            case 3: // checa las unidades
                                if (substr($xaux, 2, 1) < 1) { // si la unidad es cero, ya no hace nada

                                } else {
                                    $key = (int)substr($xaux, 2, 1);
                                    $xseek = $xarray[$key]; // obtengo directamente el valor de la unidad (del uno al nueve)
                                    $xsub = Util::subfijo($xaux);
                                    $xcadena = " " . $xcadena . " " . $xseek . " " . $xsub;
                                } // ENDIF (substr($xaux, 2, 1) < 1)
                                break;
                        } // END SWITCH
                    } // END FOR
                    $xi = $xi + 3;
                } // ENDDO

                if (substr(trim($xcadena), -5, 5) == "ILLON") // si la cadena obtenida termina en MILLON o BILLON, entonces le agrega al final la conjuncion DE
                    $xcadena .= " DE";

                if (substr(trim($xcadena), -7, 7) == "ILLONES") // si la cadena obtenida en MILLONES o BILLONES, entoncea le agrega al final la conjuncion DE
                    $xcadena .= " DE";

                // ----------- esta línea la puedes cambiar de acuerdo a tus necesidades o a tu país -------
                if (trim($xaux) != "") {
                    switch ($xz) {
                        case 0:
                            if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                                $xcadena .= "UN BILLON ";
                            else
                                $xcadena .= " BILLONES ";
                            break;
                        case 1:
                            if (trim(substr($XAUX, $xz * 6, 6)) == "1")
                                $xcadena .= "UN MILLON ";
                            else
                                $xcadena .= " MILLONES ";
                            break;
                        case 2:
                            if ($xcifra < 1) {
                                $xcadena = "CERO $xdecimales/100 ".$moneda;
                            }
                            if ($xcifra >= 1 && $xcifra < 2) {
                                $xcadena = "UN $xdecimales/100 ".$moneda;
                            }
                            if ($xcifra >= 2) {
                                $xcadena .= " $xdecimales/100 ".$moneda; //
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
            return trim($xcadena);

}

   public static  function subfijo($xx)
    { // esta función regresa un subfijo para la cifra
            $xx = trim($xx);
            $xstrlen = strlen($xx);
            if ($xstrlen == 1 || $xstrlen == 2 || $xstrlen == 3)
                $xsub = "";
            //
            if ($xstrlen == 4 || $xstrlen == 5 || $xstrlen == 6)
                $xsub = "MIL";
            //
            return $xsub;
    }

    public static function calculaDigitoMod11($numDado, $numDig, $limMult, $x10) {
		if (!$x10) {
			$numDig = 1;
		}
		$dado = $numDado;
		for ($n = 1; $n <= $numDig; $n++) {
			$soma = 0;
			$mult = 2;
			for ($i = strlen($dado) - 1; $i >= 0; $i--) {
				$soma += $mult * intval(substr($dado, $i, 1));
				if (++$mult > $limMult) {
					$mult = 2;
				}
			}
			if ($x10) {
				$dig = fmod(fmod(($soma * 10), 11), 10);
			} else {
				$dig = fmod($soma, 11);
				if ($dig == 10) {
					//$dig = "X";
					$dig = "1";
				}
			}
			$dado .= strval($dig);
		}
		return substr($dado, strlen($dado) - $numDig);
	}

	public static function str_baseconvert($str, $frombase = 10, $tobase = 36) {
		$str = trim($str);
		if (intval($frombase) != 10) {
			$len = strlen($str);
			$q = 0;
			for ($i = 0; $i < $len; $i++) {
				$r = base_convert($str[$i], $frombase, 10);
				$q = bcadd(bcmul($q, $frombase), $r);
			}
		} else {
			$q = $str;
		}

		if (intval($tobase) != 10) {
			$s = '';
			while (bccomp($q, '0', 0) > 0) {
				$r = intval(bcmod($q, $tobase));
				$s = base_convert($r, 10, $tobase) . $s;
				$q = bcdiv($q, $tobase, 0);
			}
		} else {
			$s = $q;
		}
		return $s;
	}
	public static function HoraSincronizacionCOMEX($comex, $puntoventaUser) {

		$puntoVentaCodigo=$puntoventaUser->puntoventa->codigo;
        $puntoVentaCuis=$puntoventaUser->puntoventa->cuis;
        $sucursalCodigo = $puntoventaUser->puntoventa->sucursal->codigo;
        $puntoVentaCUFD=$puntoventaUser->puntoventa->cufd;
        $puntoVentaCodigoControl=$puntoventaUser->puntoventa->codigo_control;

		$TOKEN = $comex->token_api;
		$NIT = $comex->nit;
		$CODIGO_AMBIENTE =	$comex->codigo_ambiente;
		$CODIGO_SISTEMA = $comex->codigo_sistema;
		$CODIGO_MODALIDAD = $comex->codigo_modalidad;
		$CODIGO_SUCURSAL =$sucursalCodigo;// $comex->codigo_sucursal;
		$CODIGO_SECTOR =	$comex->documentoSector->param_codigo;
		$CUIS = $puntoVentaCuis;//$comex->cuis;

        $CODIGO_EMISION = $comex->codigo_emision;

		$URL_SICRONOZACION = $comex->url_serv_impuestos.'/FacturacionSincronizacion?wsdl';





         if ($CODIGO_EMISION==2) { // 1 Online, 2 Offline, 3 Masivo
            Carbon::setLocale('es');
           // date_default_timezone_set('America/La_Paz');
            $date = Carbon::now('UTC')->setTimezone('America/La_Paz');
           // $date->toISOString();
           // $date->format('l jS \\of F Y h:i:s A');
            //2022-06-26T12:54:27.375542Z
            //2022-06-26T15:40:20-04:0
            //2022-06-26 15:42:27.996296
            //2022-06-26T08:48:20.625
            //2022-06-26T23:59:23.520

           // return $date->format("Y-m-d\\TH:i:s.u");
           /// $dattt= $date->format("Y-m-d\\TH:i:s.u");
            $dateServe= $date->format('Y-m-d\TH:i:s.v');

            return $dateServe;
            
          
         }



		ini_set("soap.wsdl_cache_enabled", "0");
		try {
			//PARA SINCRONIZAR FECHA Y HORA
			$token = $TOKEN;// session::get('token_sin');
			$opts_token = array(
				'http' => array(
					'header' => "apikey:TokenApi $TOKEN",
				),
			);
			$context = stream_context_create($opts_token);
			$header = array(
                'stream_context' => $context, 
                'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 
                'encoding' => 'UTF-8', 
                'trace' => true, 
                'connection_timeout' => 180, // verificar
                'exceptions' => true, 
                'cache_wsdl' => WSDL_CACHE_NONE, 
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
                'soap_version' => 'SOAP_1_1');
           // ini_set('default_socket_timeout', 1);
			$client = new \SoapClient($URL_SICRONOZACION, $header);

			$params = array('SolicitudSincronizacion' => array(
				'codigoAmbiente' => $CODIGO_AMBIENTE,
				'codigoSistema' => $CODIGO_SISTEMA,
				'codigoModalidad' => $CODIGO_MODALIDAD,
				'codigoPuntoVenta' => $puntoVentaCodigo,// 0,
				'codigoSucursal' => $CODIGO_SUCURSAL,
				'codigoDocumentoSector' => $CODIGO_SECTOR,
				'codigoEmision' => $CODIGO_EMISION,
				'cuis' => $CUIS,
				'nit' => $NIT),
			);
			$result = $client->__getLastResponseHeaders();
			$client->__getTypes();
			$client->__getFunctions();
			$functions = $client->__getFunctions();
			$result_sincronizacion = $client->__soapCall('sincronizarFechaHora', array($params));
           
		//	return $result_sincronizacion->RespuestaFechaHora->;


 try {
            $fecha_hora_sincronizacion = $result_sincronizacion->RespuestaFechaHora->fechaHora;
           
            return $fecha_hora_sincronizacion;

        } catch (\Throwable $th) {
            $result= array(
                'code' => 406,
                'message'=> "Error al obtener la fecha y hora. "
                 );
                return response()->json($result,406);
        }




		} catch (SoapFault $e) {
			$result= array(
                'code' => 406,
                'message'=> $e->getMessage()
                 );
                return response()->json($result,406);
		}
	}

	public static function HoraSincronizacion($codigo_ambiente, $codigo_sistema, $codigo_modalidad, $nit, $sucursal, $cuis) {
		ini_set("soap.wsdl_cache_enabled", "0");
		try {
			//PARA SINCRONIZAR FECHA Y HORA
			$wsdl_sincronizacion = session::get('url_sincronizacion');
			$token = session::get('token_sin');
			$opts_token = array(
				'http' => array(
					'header' => "apikey:TokenApi $token",
				),
			);
			$context = stream_context_create($opts_token);
			$header = array('stream_context' => $context, 'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, 'encoding' => 'UTF-8', 'trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_NONE, 'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 'soap_version' => 'SOAP_1_1');
			$client = new \SoapClient($wsdl_sincronizacion, $header);

			$params = array('SolicitudSincronizacion' => array(
				'codigoAmbiente' => $codigo_ambiente,
				'codigoSistema' => $codigo_sistema,
				'codigoModalidad' => $codigo_modalidad,
				'codigoPuntoVenta' => 0,
				'codigoSucursal' => $sucursal,
				'codigoDocumentoSector' => 1,
				'codigoEmision' => 1,
				'cuis' => $cuis,
				'nit' => $nit),
			);
			$result = $client->__getLastResponseHeaders();
			$client->__getTypes();
			$client->__getFunctions();
			$functions = $client->__getFunctions();
			$result_sincronizacion = $client->__soapCall('sincronizarFechaHora', array($params));
			return $result_sincronizacion->RespuestaFechaHora;
		} catch (SoapFault $e) {
			$result= array(
                'code' => 406,
                'message'=> $e->getMessage()
                 );
                return response()->json($result,406);
		}
	}


public static function createXML($factura,$comexData, $fechaEmision, $cuf)
    {

        $user =null;// Auth::user();
        $codigoActividad = $comexData->actividad->param_codigo;

        $tabla= Parametrica::where('param_tabla', 'leyenda_factura')->first();
        $leyendas= Parametrica::select('id','param_foranea','param_codigo', 'param_codigo_categoria','param_nombre')->where('param_foranea',$tabla->id)->where('param_codigo',$codigoActividad)->orderBy('created_at', 'asc')->get();
        $leyenda = $leyendas[4]->param_nombre;
        $totalDetalle = $factura->detalles->sum('total');
        $total = $totalDetalle;


       
       $output=View::make('factura.facturaElectronicaXML', compact('factura','cuf','fechaEmision','comexData','user','total','leyenda'))->render();
    
       return $output;
    }

    public static  function generateCUF($factura, $comexData, $fecha, $puntoventaUser)
    {

		$puntoVentaCodigo=$puntoventaUser->puntoventa->codigo;
        $puntoVentaCuis=$puntoventaUser->puntoventa->cuis;
        $sucursalCodigo = $puntoventaUser->puntoventa->sucursal->codigo;
        $puntoVentaCUFD=$puntoventaUser->puntoventa->cufd;
        $puntoVentaCodigoControl=$puntoventaUser->puntoventa->codigo_control;

		$NIT = $comexData->nit;
        $CODIGO_EMISION = $comexData->codigo_emision;

		$feh= str_replace('T','',$fecha );
		$feh2= str_replace(':','',$feh );
		$feh3= str_replace('-','',$feh2 );
		$feh4= str_replace('.','',$feh3 );


        $sucursal=$sucursalCodigo; //$comexData->codigo_sucursal;
        $modalidad=$comexData->codigo_modalidad;
        $tipoEmision = $CODIGO_EMISION; // 1 Online, 2 Offline, 3 Masivo
        $tipoFactura= 2; //1 = Factura con Derecho a Crédito Fiscal,2 = Factura sin Derecho a Crédito Fiscal, 3 = Documento de Ajuste  
        $tipoDocumentoSector=$comexData->documentoSector->param_codigo;//'3';//FACTURA COMERCIAL DE EXPORTACIÓN
        $nroFactura=  $factura->nro_factura;
        $puntoVenta=$puntoVentaCodigo;// $comexData->codigo_punto_venta;
        $codigoAutoVerificador='';

        $nitEmisorC_ = Util::completarCeros($NIT, 13);
        $fechaHoraC_ = Util::completarCeros($feh4, 17);
        $sucursalC_ = Util::completarCeros($sucursal, 4);
        $modalidadC_ = Util::completarCeros($modalidad, 1);
        $tipoEmisionC_ = Util::completarCeros($tipoEmision, 1);
        $tipoFacturaC_ = Util::completarCeros($tipoFactura, 1);
        $tipoDocumentoSectorC_ = Util::completarCeros($tipoDocumentoSector, 2);
        $nroFacturaC_ = Util::completarCeros($nroFactura, 10);
        $puntoVentaC_ = Util::completarCeros($puntoVenta, 4);

        $nroContact2=$nitEmisorC_.''.$fechaHoraC_.''.$sucursalC_.''.$modalidadC_.''.$tipoEmisionC_.''.$tipoFacturaC_.''.$tipoDocumentoSectorC_.''.$nroFacturaC_.''.$puntoVentaC_.''.$codigoAutoVerificador; 

        $nroContact2Nodulo11= Util::calculaDigitoMod11($nroContact2, 1, 9, false);
        $nroContact3=$nroContact2.''.$nroContact2Nodulo11;

        $base16 =  Util::str_baseconvert($nroContact3,10,16);

      //  return strtoupper($base16).''.$comexData->codigo_control;
        return strtoupper($base16).''.$puntoVentaCodigoControl;

    }




	public static function completarCeros($nro, $nroCeros){
        $arrayNro = str_split($nro);
        $countNro =count($arrayNro);
        $resp="";
        $nroCeros= $nroCeros - $countNro;
        for ($i=0 ; $i < $nroCeros; $i++) { 
            $resp = $resp.''.'0';
        }
        return $resp.$nro;
    }

}
