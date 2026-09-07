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

        // 1. Catálogos base normativos
        $catalogos = [
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
                ['codigo' => '3', 'descripcion' => 'CHEQUE'],
                ['codigo' => '4', 'descripcion' => 'VALES'],
                ['codigo' => '5', 'descripcion' => 'OTROS'],
                ['codigo' => '6', 'descripcion' => 'PAGO POSTERIOR'],
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
                ['codigo' => '1', 'descripcion' => 'BOBINAS'],
                ['codigo' => '2', 'descripcion' => 'BALDE'],
                ['codigo' => '3', 'descripcion' => 'BARRILES'],
                ['codigo' => '4', 'descripcion' => 'BOLSA'],
                ['codigo' => '5', 'descripcion' => 'BOTELLAS'],
                ['codigo' => '6', 'descripcion' => 'CAJA'],
            ],
            'EVENTO_SIGNIFICATIVO' => [
                ['codigo' => '1', 'descripcion' => 'CORTE DEL SERVICIO DE ENERGÍA ELÉCTRICA'],
                ['codigo' => '2', 'descripcion' => 'CORTE DEL SERVICIO DE INTERNET'],
                ['codigo' => '3', 'descripcion' => 'INACCESIBILIDAD AL SERVICIO WEB DE LA ADMINISTRACIÓN TRIBUTARIA'],
                ['codigo' => '4', 'descripcion' => 'INGRESO A ZONAS SIN INTERNET O SIN COBERTURA'],
                ['codigo' => '5', 'descripcion' => 'VENTA EN LUGARES SIN INTERNET'],
                ['codigo' => '6', 'descripcion' => 'FALLA DE SOFTWARE O SISTEMA DE FACTURACIÓN'],
                ['codigo' => '7', 'descripcion' => 'FALLA DE HARDWARE'],
            ],
        ];

        $totalSincronizados = 0;

        foreach ($catalogos as $tipoCatalogo => $items) {
            $this->line("Sincronizando catálogo: {$tipoCatalogo}");
            foreach ($items as $item) {
                SiatCatalogo::updateOrCreate(
                    [
                        'tipo_catalogo' => $tipoCatalogo,
                        'codigo' => $item['codigo'],
                    ],
                    [
                        'descripcion' => $item['descripcion'],
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'SINCRONIZAR',
                        '_usuario_creacion' => 1,
                    ]
                );
                $totalSincronizados++;
            }
        }

        $this->info("Sincronización finalizada exitosamente. Total registros procesados: {$totalSincronizados}");

        return Command::SUCCESS;
    }
}
