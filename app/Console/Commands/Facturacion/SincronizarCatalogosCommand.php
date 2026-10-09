<?php

declare(strict_types=1);

namespace App\Console\Commands\Facturacion;

use App\Models\Facturacion\SiatCatalogo;
use App\Services\Facturacion\SiatSoapService;
use Illuminate\Console\Command;

class SincronizarCatalogosCommand extends Command
{
    protected $signature = 'facturacion:sincronizar-catalogos';

    protected $description = 'Sincroniza y actualiza los catálogos paramétricos del SIAT (Impuestos Nacionales)';

    public function handle(SiatSoapService $soapService): int
    {
        $this->info('Iniciando sincronización de catálogos paramétricos del SIAT...');

        // 1. Obtener CUIS vigente
        $cuisModel = \App\Models\Facturacion\SiatCuis::where('_estado', 'ACTIVO')
            ->where('fecha_vigencia', '>', now())
            ->latest('id')
            ->first();

        $cuis = $cuisModel ? $cuisModel->codigo : null;
        if (!$cuis) {
            $respCuis = $soapService->solicitarCuis(0, 0);
            if (!empty($respCuis['success'])) {
                $cuis = $respCuis['cuis'];
                \App\Models\Facturacion\SiatCuis::create([
                    'id_sucursal' => 1,
                    'codigo' => $cuis,
                    'fecha_vigencia' => \Carbon\Carbon::parse($respCuis['fecha_vigencia']),
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'SYNC_CATALOGO',
                    '_usuario_creacion' => 1,
                ]);
            } else {
                $cuis = '6D4A1883'; // CUIS piloto conocido
            }
        }

        $totalSincronizados = 0;
        $exitoRemoto = false;

        // Mapeo de métodos paramétricos estándar
        $parametricasMetodos = [
            'sincronizarParametricaTipoDocumentoIdentidad' => 'TIPO_DOCUMENTO_IDENTIDAD',
            'sincronizarParametricaTipoMetodoPago' => 'METODO_PAGO',
            'sincronizarParametricaMotivoAnulacion' => 'MOTIVO_ANULACION',
            'sincronizarParametricaUnidadMedida' => 'UNIDAD_MEDIDA',
            'sincronizarParametricaEventosSignificativos' => 'EVENTO_SIGNIFICATIVO',
            'sincronizarParametricaTipoPuntoVenta' => 'TIPO_PUNTO_VENTA',
            'sincronizarParametricaTiposFactura' => 'TIPO_FACTURA',
            'sincronizarParametricaTipoDocumentoSector' => 'DOCUMENTO_SECTOR',
            'sincronizarParametricaTipoEmision' => 'TIPO_EMISION',
            'sincronizarParametricaTipoMoneda' => 'TIPO_MONEDA',
            'sincronizarParametricaPaisOrigen' => 'PAIS_ORIGEN',
            'sincronizarListaMensajesServicios' => 'MENSAJES_SERVICIO',
        ];

        foreach ($parametricasMetodos as $metodo => $tipoCatalogo) {
            $this->line("Consultando al SIN: {$metodo}...");
            $res = $soapService->sincronizarParametricas($metodo, $cuis);

            if ($res['success'] && isset($res['data']->RespuestaListaParametricas->listaCodigos)) {
                $exitoRemoto = true;
                $lista = $res['data']->RespuestaListaParametricas->listaCodigos;
                if (!is_array($lista)) {
                    $lista = [$lista];
                }

                foreach ($lista as $item) {
                    $cod = (string) ($item->codigoClasificador ?? '');
                    $desc = (string) ($item->descripcion ?? '');
                    if ($cod !== '') {
                        SiatCatalogo::updateOrCreate(
                            ['tipo_catalogo' => $tipoCatalogo, 'codigo' => $cod],
                            ['descripcion' => mb_strtoupper($desc), '_estado' => 'ACTIVO', '_transaccion' => 'SIN_SOAP', '_usuario_creacion' => 1]
                        );
                        $totalSincronizados++;
                    }
                }
                $this->info(" -> {$tipoCatalogo}: " . count($lista) . " registros actualizados desde el SIN.");
            }
        }

        // Sincronizar Actividades Económicas
        $this->line("Consultando al SIN: sincronizarActividades...");
        $resAct = $soapService->sincronizarParametricas('sincronizarActividades', $cuis);
        if ($resAct['success'] && isset($resAct['data']->RespuestaListaActividades->listaActividades)) {
            $exitoRemoto = true;
            $listaAct = $resAct['data']->RespuestaListaActividades->listaActividades;
            if (!is_array($listaAct)) {
                $listaAct = [$listaAct];
            }
            foreach ($listaAct as $item) {
                SiatCatalogo::updateOrCreate(
                    ['tipo_catalogo' => 'ACTIVIDAD_ECONOMICA', 'codigo' => (string) $item->codigoCaeb],
                    ['descripcion' => mb_strtoupper((string) $item->descripcion), 'codigo_padre' => (string) ($item->tipoActividad ?? ''), '_estado' => 'ACTIVO', '_transaccion' => 'SIN_SOAP', '_usuario_creacion' => 1]
                );
                $totalSincronizados++;
            }
            $this->info(" -> ACTIVIDAD_ECONOMICA: " . count($listaAct) . " registros actualizados.");
        }

        // Sincronizar Lista Productos Servicios homologados
        $this->line("Consultando al SIN: sincronizarListaProductosServicios...");
        $resProd = $soapService->sincronizarParametricas('sincronizarListaProductosServicios', $cuis);
        if ($resProd['success'] && isset($resProd['data']->RespuestaListaProductos->listaCodigos)) {
            $exitoRemoto = true;
            $listaProd = $resProd['data']->RespuestaListaProductos->listaCodigos;
            if (!is_array($listaProd)) {
                $listaProd = [$listaProd];
            }
            foreach ($listaProd as $item) {
                SiatCatalogo::updateOrCreate(
                    ['tipo_catalogo' => 'PRODUCTO_SERVICIO_SIN', 'codigo' => (string) $item->codigoProducto],
                    ['descripcion' => mb_strtoupper((string) $item->descripcionProducto), 'codigo_padre' => (string) ($item->codigoActividad ?? ''), '_estado' => 'ACTIVO', '_transaccion' => 'SIN_SOAP', '_usuario_creacion' => 1]
                );
                $totalSincronizados++;
            }
            $this->info(" -> PRODUCTO_SERVICIO_SIN: " . count($listaProd) . " registros actualizados.");
        }

        // Sincronizar Leyendas
        $this->line("Consultando al SIN: sincronizarListaLeyendasFactura...");
        $resLey = $soapService->sincronizarParametricas('sincronizarListaLeyendasFactura', $cuis);
        if ($resLey['success'] && isset($resLey['data']->RespuestaListaParametricasLeyendas->listaLeyendas)) {
            $exitoRemoto = true;
            $listaLey = $resLey['data']->RespuestaListaParametricasLeyendas->listaLeyendas;
            if (!is_array($listaLey)) {
                $listaLey = [$listaLey];
            }
            $idx = 1;
            foreach ($listaLey as $item) {
                SiatCatalogo::updateOrCreate(
                    ['tipo_catalogo' => 'LEYENDA_FACTURA', 'codigo' => (string) $idx++],
                    ['descripcion' => (string) $item->descripcionLeyenda, 'codigo_padre' => (string) ($item->codigoActividad ?? ''), '_estado' => 'ACTIVO', '_transaccion' => 'SIN_SOAP', '_usuario_creacion' => 1]
                );
                $totalSincronizados++;
            }
            $this->info(" -> LEYENDA_FACTURA: " . count($listaLey) . " registros actualizados.");
        }

        // Sincronizar Actividades Documento Sector
        $this->line("Consultando al SIN: sincronizarListaActividadesDocumentoSector...");
        $resSec = $soapService->sincronizarParametricas('sincronizarListaActividadesDocumentoSector', $cuis);
        if ($resSec['success'] && isset($resSec['data']->RespuestaListaActividadesDocumentoSector->listaActividadesDocumentoSector)) {
            $exitoRemoto = true;
            $listaSec = $resSec['data']->RespuestaListaActividadesDocumentoSector->listaActividadesDocumentoSector;
            if (!is_array($listaSec)) {
                $listaSec = [$listaSec];
            }
            foreach ($listaSec as $item) {
                SiatCatalogo::updateOrCreate(
                    ['tipo_catalogo' => 'DOCUMENTO_SECTOR_AUTORIZADO', 'codigo' => (string) $item->codigoDocumentoSector],
                    ['descripcion' => (string) $item->tipoDocumentoSector, 'codigo_padre' => (string) ($item->codigoActividad ?? ''), '_estado' => 'ACTIVO', '_transaccion' => 'SIN_SOAP', '_usuario_creacion' => 1]
                );
                $totalSincronizados++;
            }
            $this->info(" -> DOCUMENTO_SECTOR_AUTORIZADO: " . count($listaSec) . " registros actualizados.");
        }

        // Si falló la comunicación remota, asegurar catálogos normativos base
        if (!$exitoRemoto) {
            $this->warn('No se pudo contactar al SIN remoto. Aplicando catálogos normativos locales de contingencia...');
            $catalogosLocales = [
                'TIPO_DOCUMENTO_IDENTIDAD' => [
                    ['codigo' => '1', 'descripcion' => 'CÉDULA DE IDENTIDAD'],
                    ['codigo' => '2', 'descripcion' => 'CÉDULA DE IDENTIDAD DE EXTRANJERO'],
                    ['codigo' => '3', 'descripcion' => 'PASAPORTE'],
                    ['codigo' => '4', 'descripcion' => 'OTRO DOCUMENTO DE IDENTIDAD'],
                    ['codigo' => '5', 'descripcion' => 'NÚMERO DE IDENTIFICACIÓN TRIBUTARIA (NIT)'],
                ],
                'METODO_PAGO' => [
                    ['codigo' => '1', 'descripcion' => 'EFECTIVO'],
                    ['codigo' => '2', 'descripcion' => 'TARJETA DE CRÉDITO/DÉBITO'],
                    ['codigo' => '7', 'descripcion' => 'TRANSFERENCIA BANCARIA'],
                    ['codigo' => '8', 'descripcion' => 'DEPÓSITO EN CUENTA'],
                    ['codigo' => '9', 'descripcion' => 'TRANSFERENCIA QR SIMPLE'],
                ],
                'MOTIVO_ANULACION' => [
                    ['codigo' => '1', 'descripcion' => 'FACTURA MAL EMITIDA'],
                    ['codigo' => '2', 'descripcion' => 'DATOS DE EMISIÓN INCORRECTOS'],
                    ['codigo' => '3', 'descripcion' => 'FACTURA O NOTA DE CRÉDITO-DÉBITO DEVUELTA'],
                    ['codigo' => '4', 'descripcion' => 'FACTURA DUPLICADA'],
                ],
                'UNIDAD_MEDIDA' => [
                    ['codigo' => '58', 'descripcion' => 'UNIDAD (BIENES / SERVICIOS)'],
                    ['codigo' => '65', 'descripcion' => 'METRO CÚBICO (M³)'],
                ],
            ];

            foreach ($catalogosLocales as $tipoCatalogo => $items) {
                foreach ($items as $item) {
                    SiatCatalogo::updateOrCreate(
                        ['tipo_catalogo' => $tipoCatalogo, 'codigo' => $item['codigo']],
                        ['descripcion' => $item['descripcion'], '_estado' => 'ACTIVO', '_transaccion' => 'LOCAL_DEFAULT', '_usuario_creacion' => 1]
                    );
                    $totalSincronizados++;
                }
            }
        }

        $this->info("Sincronización finalizada exitosamente. Total registros procesados: {$totalSincronizados}");

        return Command::SUCCESS;
    }
}
