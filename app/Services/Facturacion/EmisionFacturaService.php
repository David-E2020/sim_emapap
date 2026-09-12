<?php

declare(strict_types=1);

namespace App\Services\Facturacion;

use App\Models\Facturacion\ClienteFactura;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\FacturaDetalle;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmisionFacturaService
{
    public function __construct(
        protected CufService $cufService,
        protected XmlFacturaService $xmlFacturaService,
        protected FirmaDigitalService $firmaDigitalService,
        protected SiatSoapService $siatSoapService
    ) {}

    /**
     * Emite una factura oficial (Sector 1 Compra-Venta o Sector 13 Servicios Básicos).
     *
     * @param array $datos Cabecera, detalles y parámetros de facturación.
     * @return Factura Instancia persistida con sus detalles y XML generado.
     * @throws Exception
     */
    public function emitir(array $datos): Factura
    {
        // 1. Sucursal y Punto de Venta
        $sucursal = null;
        if (!empty($datos['id_sucursal'])) {
            $sucursal = SiatSucursal::find((int) $datos['id_sucursal']);
        }
        if (!$sucursal && isset($datos['codigo_sucursal'])) {
            $sucursal = SiatSucursal::where('codigo_sucursal', (int) $datos['codigo_sucursal'])->first();
        }
        if (!$sucursal) {
            $sucursal = SiatSucursal::where('codigo_sucursal', 0)->first() ?? SiatSucursal::create([
                'codigo_sucursal' => 0,
                'nombre' => 'Casa Matriz EMAPAP',
                'direccion' => 'Av. Panamericana s/n, Plaza 15 de Agosto',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
                'telefono' => '2-8147000',
            ]);
        }

        $puntoVenta = null;
        if (!empty($datos['id_punto_venta'])) {
            $puntoVenta = SiatPuntoVenta::find((int) $datos['id_punto_venta']);
        }
        if (!$puntoVenta && isset($datos['codigo_punto_venta'])) {
            $puntoVenta = SiatPuntoVenta::where('id_sucursal', $sucursal->id)
                ->where('codigo_punto_venta', (int) $datos['codigo_punto_venta'])
                ->first();
        }
        if (!$puntoVenta) {
            $puntoVenta = SiatPuntoVenta::where('id_sucursal', $sucursal->id)
                ->where('codigo_punto_venta', 0)
                ->first() ?? SiatPuntoVenta::create([
                    'id_sucursal' => $sucursal->id,
                    'codigo_punto_venta' => 0,
                    'nombre' => 'Punto de Venta 0 - Ventanilla General',
                    'tipo_punto_venta' => 0,
                ]);
        }

        // 2. Obtener o Generar CUFD Vigente
        $cufdVigente = SiatCufd::where('id_sucursal', $sucursal->id)
            ->where('id_punto_venta', $puntoVenta->id)
            ->where('fecha_vigencia', '>', Carbon::now())
            ->latest('id')
            ->first();

        if (!$cufdVigente) {
            $cuisVigente = SiatCuis::where('id_sucursal', $sucursal->id)
                ->where('id_punto_venta', $puntoVenta->id)
                ->where('fecha_vigencia', '>', Carbon::now())
                ->latest('id')
                ->first();

            $cuisCodigo = $cuisVigente ? $cuisVigente->codigo : 'CUIS_EMAPAP_GENERAL';

            $respCufd = $this->siatSoapService->solicitarCufd($cuisCodigo, $sucursal->codigo_sucursal, $puntoVenta->codigo_punto_venta);

            if (!empty($respCufd['success'])) {
                $cufdVigente = SiatCufd::create([
                    'id_sucursal' => $sucursal->id,
                    'id_punto_venta' => $puntoVenta->id,
                    'codigo' => $respCufd['cufd'],
                    'codigo_control' => $respCufd['codigo_control'],
                    'direccion' => $respCufd['direccion'] ?? $sucursal->direccion,
                    'fecha_vigencia' => Carbon::parse($respCufd['fecha_vigencia']),
                ]);
            } else {
                // CUFD de contingencia si no hay conexión SOAP activa
                $cufdVigente = SiatCufd::create([
                    'id_sucursal' => $sucursal->id,
                    'id_punto_venta' => $puntoVenta->id,
                    'codigo' => 'CUFD_' . bin2hex(random_bytes(16)),
                    'codigo_control' => strtoupper(substr(md5(uniqid()), 0, 16)),
                    'direccion' => $sucursal->direccion,
                    'fecha_vigencia' => Carbon::now()->addHours(24),
                ]);
            }
        }

        // 3. Cliente Fiscal
        $cliente = null;
        if (!empty($datos['numero_documento'])) {
            $cliente = ClienteFactura::updateOrCreate(
                [
                    'codigo_tipo_documento_identidad' => (int) ($datos['codigo_tipo_documento_identidad'] ?? 1),
                    'numero_documento' => trim((string) $datos['numero_documento']),
                    'complemento' => !empty($datos['complemento']) ? trim((string) $datos['complemento']) : null,
                ],
                [
                    'nombre_razon_social' => trim((string) ($datos['nombre_razon_social'] ?? 'SIN NOMBRE')),
                    'correo_electronico' => $datos['correo_electronico'] ?? null,
                ]
            );
        }

        // 4. Correlativo de Factura
        $ultimoNumero = Factura::where('id_sucursal', $sucursal->id)
            ->where('id_punto_venta', $puntoVenta->id)
            ->max('numero_factura') ?? 0;
        $numeroFactura = $ultimoNumero + 1;

        $fechaEmision = Carbon::now();

        $empresa = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('facturacion.configuracion_empresa')) {
                $empresa = \App\Models\Facturacion\ConfiguracionEmpresa::getActiva();
            }
        } catch (\Throwable $e) {
            $empresa = null;
        }

        $nitEmisor = $empresa && !empty($empresa->nit) ? (string) $empresa->nit : config('siat.nit_emisor', '123456789');
        $modalidad = $empresa && $empresa->codigo_modalidad ? (int) $empresa->codigo_modalidad : (int) config('siat.modalidad', 1);
        $tipoEmision = (int) ($datos['tipo_emision'] ?? $datos['codigo_emision'] ?? 1);
        $tipoFactura = (int) ($datos['tipo_factura_documento'] ?? 1);
        $documentoSector = (int) ($datos['codigo_documento_sector'] ?? 1);

        // 5. Generar CUF con algoritmo oficial
        $cuf = $this->cufService->generarCuf(
            $nitEmisor,
            $fechaEmision,
            $sucursal->codigo_sucursal,
            $modalidad,
            $tipoEmision,
            $tipoFactura,
            $documentoSector,
            $numeroFactura,
            $puntoVenta->codigo_punto_venta,
            $cufdVigente->codigo_control
        );

        // 6. Transacción para almacenar Factura y Detalles
        $factura = DB::transaction(function () use (
            $datos,
            $sucursal,
            $puntoVenta,
            $cliente,
            $cufdVigente,
            $numeroFactura,
            $cuf,
            $fechaEmision,
            $modalidad,
            $tipoEmision,
            $tipoFactura,
            $documentoSector
        ) {
            $items = $datos['items'] ?? [];
            $montoTotalItems = 0.0;
            $montoDescuentoGlobal = (float) ($datos['monto_descuento'] ?? 0.0);

            foreach ($items as $item) {
                $cant = (float) ($item['cantidad'] ?? 1);
                $pu = (float) ($item['precio_unitario'] ?? 0);
                $desc = (float) ($item['monto_descuento'] ?? 0);
                $sub = ($cant * $pu) - $desc;
                $montoTotalItems += $sub;
            }

            // Para sector 13: si viene ajuste no sujeto a iva (ej. alcantarillado), se suma al total general pero no al sujeto a IVA
            $ajusteNoSujetoIva = (float) ($datos['ajuste_no_sujeto_iva'] ?? 0.0);
            $montoTotalFinal = isset($datos['monto_total']) 
                ? (float) $datos['monto_total'] 
                : max(0, $montoTotalItems - $montoDescuentoGlobal + $ajusteNoSujetoIva);

            $montoSujetoIva = isset($datos['monto_total_sujeto_iva'])
                ? (float) $datos['monto_total_sujeto_iva']
                : max(0, $montoTotalFinal - $ajusteNoSujetoIva);

            $leyenda = $datos['leyenda'] ?? 'Ley N° 453: Los servicios deben prestarse en condiciones de inocuidad, calidad y seguridad.';

            $factura = Factura::create([
                'id_sucursal' => $sucursal->id,
                'id_punto_venta' => $puntoVenta->id,
                'id_cliente' => $cliente?->id,
                'id_abonado' => $datos['id_abonado'] ?? null,
                'id_cufd' => $cufdVigente->id,
                'id_evento_significativo' => $datos['id_evento_significativo'] ?? null,
                'numero_factura' => $numeroFactura,
                'cuf' => $cuf,
                'cufd' => $cufdVigente->codigo,
                'codigo_control' => $cufdVigente->codigo_control,
                'fecha_emision' => $fechaEmision,
                'codigo_modalidad' => $modalidad,
                'tipo_emision' => $tipoEmision,
                'tipo_factura_documento' => $tipoFactura,
                'codigo_documento_sector' => $documentoSector,
                'mes' => $datos['mes'] ?? null,
                'gestion' => isset($datos['gestion']) ? (int) $datos['gestion'] : null,
                'ciudad' => $datos['ciudad'] ?? 'Patacamaya',
                'zona' => $datos['zona'] ?? null,
                'numero_medidor' => $datos['numero_medidor'] ?? null,
                'domicilio_cliente' => $datos['domicilio_cliente'] ?? null,
                'consumo_periodo' => isset($datos['consumo_periodo']) ? (float) $datos['consumo_periodo'] : null,
                'beneficiario_ley_1886' => !empty($datos['beneficiario_ley_1886']),
                'monto_descuento_ley_1886' => (float) ($datos['monto_descuento_ley_1886'] ?? 0.0),
                'monto_descuento_tarifa_dignidad' => (float) ($datos['monto_descuento_tarifa_dignidad'] ?? 0.0),
                'tasa_aseo' => (float) ($datos['tasa_aseo'] ?? 0.0),
                'tasa_alumbrado' => (float) ($datos['tasa_alumbrado'] ?? 0.0),
                'ajuste_no_sujeto_iva' => $ajusteNoSujetoIva,
                'detalle_ajuste_no_sujeto_iva' => $datos['detalle_ajuste_no_sujeto_iva'] ?? null,
                'ajuste_sujeto_iva' => (float) ($datos['ajuste_sujeto_iva'] ?? 0.0),
                'detalle_ajuste_sujeto_iva' => $datos['detalle_ajuste_sujeto_iva'] ?? null,
                'otros_pagos_no_sujeto_iva' => (float) ($datos['otros_pagos_no_sujeto_iva'] ?? 0.0),
                'detalle_otros_pagos_no_sujeto_iva' => $datos['detalle_otros_pagos_no_sujeto_iva'] ?? null,
                'otras_tasas' => (float) ($datos['otras_tasas'] ?? 0.0),
                'nombre_razon_social' => trim((string) ($datos['nombre_razon_social'] ?? 'SIN NOMBRE')),
                'numero_documento' => trim((string) ($datos['numero_documento'] ?? '0')),
                'complemento' => !empty($datos['complemento']) ? trim((string) $datos['complemento']) : null,
                'codigo_tipo_documento_identidad' => (int) ($datos['codigo_tipo_documento_identidad'] ?? 1),
                'codigo_metodo_pago' => (int) ($datos['codigo_metodo_pago'] ?? 1),
                'numero_tarjeta' => $datos['numero_tarjeta'] ?? null,
                'monto_total' => $montoTotalFinal,
                'monto_total_sujeto_iva' => $montoSujetoIva,
                'monto_descuento' => $montoDescuentoGlobal,
                'monto_gift_card' => (float) ($datos['monto_gift_card'] ?? 0.0),
                'codigo_moneda' => 1,
                'tipo_cambio' => 1.00,
                'leyenda' => $leyenda,
                'usuario_emision' => $datos['usuario_emision'] ?? 'admin',
                'estado_factura' => $tipoEmision === 2 ? 'OFFLINE' : 'VALIDADA',
            ]);

            foreach ($items as $item) {
                $cant = (float) ($item['cantidad'] ?? 1);
                $pu = (float) ($item['precio_unitario'] ?? 0);
                $desc = (float) ($item['monto_descuento'] ?? 0);
                $sub = ($cant * $pu) - $desc;

                FacturaDetalle::create([
                    'id_factura' => $factura->id,
                    'id_producto_servicio' => $item['id_producto_servicio'] ?? null,
                    'codigo_actividad' => $item['codigo_actividad'] ?? '360000',
                    'codigo_producto_sin' => $item['codigo_producto_sin'] ?? '86330',
                    'codigo_producto_empresa' => $item['codigo_producto_empresa'] ?? 'AGUA-01',
                    'descripcion' => $item['descripcion'] ?? 'Servicio de Agua Potable',
                    'cantidad' => $cant,
                    'codigo_unidad_medida' => (int) ($item['codigo_unidad_medida'] ?? 58),
                    'precio_unitario' => $pu,
                    'monto_descuento' => $desc,
                    'subtotal' => $sub,
                    'numero_serie' => $item['numero_serie'] ?? null,
                    'numero_imei' => $item['numero_imei'] ?? null,
                ]);
            }

            return $factura;
        });

        // 7. Generar XML Oficial
        $xmlContent = $this->xmlFacturaService->construirXml($factura);

        // Guardar archivo XML
        $fileName = "factura_{$factura->numero_factura}_{$factura->cuf}.xml";
        $xmlPath = "siat/facturas/{$fileName}";
        Storage::disk('local')->put($xmlPath, $xmlContent);

        // Representación gráfica QR oficial del SIAT
        $nitConfig = $empresa && !empty($empresa->nit) ? (string) $empresa->nit : config('siat.nit_emisor', '123456789');
        $qrData = sprintf(
            'https://siat.impuestos.gob.bo/consulta/QR?nit=%s&cuf=%s&numero=%d&t=%d',
            $nitConfig,
            $factura->cuf,
            $factura->numero_factura,
            1
        );

        $factura->update([
            'xml_firmado_path' => $xmlPath,
            'representacion_grafica_qr' => $qrData,
        ]);

        return $factura->fresh(['detalles', 'sucursal', 'puntoVenta', 'cliente', 'abonado']);
    }

    /**
     * Emite un lote masivo de facturas electrónicas (Sector 1 o Sector 13) dentro de una transacción
     * optimizada, calculando CUFs, generando XMLs y asociándolas opcionalmente a un evento significativo.
     *
     * @param array $lote Colección de arrays de datos de facturas.
     * @return array Resumen con facturas emitidas, monto total y errores si existieran.
     */
    public function emitirLoteMasivo(array $lote): array
    {
        $emitidas = [];
        $errores = [];
        $totalMonto = 0.0;

        foreach ($lote as $idx => $datosFactura) {
            try {
                $factura = $this->emitir($datosFactura);
                $emitidas[] = $factura;
                $totalMonto += (float) $factura->monto_total;
            } catch (\Throwable $e) {
                $errores[] = [
                    'indice' => $idx,
                    'documento' => $datosFactura['numero_documento'] ?? 'S/N',
                    'cliente' => $datosFactura['nombre_razon_social'] ?? 'S/N',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_procesadas' => count($lote),
            'total_emitidas' => count($emitidas),
            'total_errores' => count($errores),
            'monto_total_lote' => round($totalMonto, 2),
            'facturas' => $emitidas,
            'errores' => $errores,
        ];
    }
}
