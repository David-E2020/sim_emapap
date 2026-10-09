<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\Facturacion\EventoSignificativo;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\FacturaPaquete;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FacturacionIntegralWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected string $token;
    protected SiatSucursal $sucursal;
    protected SiatPuntoVenta $puntoVenta;
    protected SiatCufd $cufd;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->admin);

        $this->sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            [
                'nombre' => 'Casa Matriz EMAPAP',
                'direccion' => 'Av. Panamericana s/n, Plaza 15 de Agosto',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
                'telefono' => '2-8147000',
            ]
        );

        $this->puntoVenta = SiatPuntoVenta::firstOrCreate(
            [
                'id_sucursal' => $this->sucursal->id,
                'codigo_punto_venta' => 0,
            ],
            [
                'nombre' => 'Punto de Venta 0 - Ventanilla Principal',
                'tipo_punto_venta' => 0,
            ]
        );

        SiatCuis::firstOrCreate(
            [
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
            ],
            [
                'codigo' => 'CUIS_EMAPAP_TEST',
                'fecha_vigencia' => Carbon::now()->addMonths(6),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]
        );

        $this->cufd = SiatCufd::firstOrCreate(
            [
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
                'codigo_control' => 'B1382C89A',
            ],
            [
                'codigo' => 'CUFD_EMAPAP_TEST_2026',
                'direccion' => 'Av. Panamericana s/n',
                'fecha_vigencia' => Carbon::now()->addHours(24),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]
        );
    }

    /**
     * Test 1: Conectividad, Hora oficial y Verificación de NIT
     */
    public function test_conectividad_sincronizacion_hora_y_verificacion_nit(): void
    {
        // 1. Estado de conexión
        $respConexion = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/facturacion/siat/estado-conexion');

        $respConexion->assertStatus(200)
            ->assertJsonStructure(['success', 'data', 'message']);

        // 2. Sincronización de hora oficial
        $respHora = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/facturacion/siat/sincronizar-hora');

        $respHora->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'en_tolerancia' => true,
                ],
            ]);

        // 3. Verificación de NIT en el padrón tributario
        $respNit = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/facturacion/siat/verificar-nit/123456789');

        $respNit->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Test 2: Emisión individual Sector 13 (Servicios Básicos - Agua Potable) con XML y QR
     */
    public function test_emision_individual_sector13_servicios_basicos_y_qr(): void
    {
        $payload = [
            'codigo_documento_sector' => 13,
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->puntoVenta->id,
            'nombre_razon_social' => 'MAMANI CONDORI JUAN',
            'numero_documento' => '6543210',
            'codigo_tipo_documento_identidad' => 1,
            'codigo_metodo_pago' => 1,
            'mes' => 'SEPTIEMBRE',
            'gestion' => 2026,
            'ciudad' => 'Patacamaya',
            'zona' => 'ZONA CENTRAL',
            'numero_medidor' => 'MED-9021',
            'consumo_periodo' => 15.5,
            'beneficiario_ley_1886' => true,
            'monto_descuento_ley_1886' => 4.50,
            'items' => [
                [
                    'descripcion' => 'SERVICIO DE AGUA POTABLE - CATEGORIA DOMESTICA',
                    'cantidad' => 1,
                    'precio_unitario' => 35.00,
                    'monto_descuento' => 0,
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86330',
                    'codigo_producto_empresa' => 'AGUA-DOM',
                    'codigo_unidad_medida' => 58,
                ],
                [
                    'descripcion' => 'SERVICIO DE ALCANTARILLADO SANITARIO (40%)',
                    'cantidad' => 1,
                    'precio_unitario' => 14.00,
                    'monto_descuento' => 0,
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86330',
                    'codigo_producto_empresa' => 'ALC-SAN',
                    'codigo_unidad_medida' => 58,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/facturacion/facturas', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Factura emitida y registrada exitosamente.',
            ]);

        $factura = $response->json('data');
        $this->assertNotEmpty($factura['cuf']);
        $this->assertStringEndsWith('B1382C89A', $factura['cuf']);
        $this->assertEquals(13, $factura['codigo_documento_sector']);
        $this->assertStringStartsWith('https://siat.impuestos.gob.bo/consulta/QR?', $factura['representacion_grafica_qr']);

        // Verificar que el XML se haya generado y guardado en almacenamiento local
        $xmlPath = $factura['xml_firmado_path'];
        $this->assertTrue(Storage::disk('local')->exists($xmlPath));
        $xmlContent = Storage::disk('local')->get($xmlPath);
        $tagEsperado = (int) ($factura['codigo_modalidad'] ?? 1) === 2 ? 'facturaComputarizadaServicioBasico' : 'facturaElectronicaServicioBasico';
        $this->assertStringContainsString($tagEsperado, $xmlContent);
        $this->assertStringContainsString('MED-9021', $xmlContent);
    }

    /**
     * Test 3: Flujo Completo de Contingencia (Apertura de Evento, Facturas Offline, .tar.gz real y Validación)
     */
    public function test_flujo_integral_contingencia_emision_offline_empaquetado_tar_gz_y_validacion(): void
    {
        // 1. Iniciar Evento Significativo (Corte de Internet = Código 2)
        $respInicio = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/facturacion/eventos-significativos', [
                'codigo_evento' => 2,
                'descripcion' => 'Corte del enlace troncal de fibra óptica de la EPSA',
                'id_sucursal' => $this->sucursal->codigo_sucursal,
                'id_punto_venta' => $this->puntoVenta->codigo_punto_venta,
            ]);

        $respInicio->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Evento significativo iniciado. El sistema ha entrado en modo contingencia.',
            ]);

        $eventoId = $respInicio->json('data.id');
        $this->assertNotNull($eventoId);

        // 2. Emitir 2 facturas en modo Contingencia (codigo_emision = 2) asociadas al evento
        for ($i = 1; $i <= 2; $i++) {
            $respFactura = $this->withHeader('Authorization', "Bearer {$this->token}")
                ->postJson('/api/facturacion/facturas', [
                    'codigo_documento_sector' => 1,
                    'codigo_emision' => 2, // Fuera de línea
                    'id_evento_significativo' => $eventoId,
                    'id_sucursal' => $this->sucursal->id,
                    'id_punto_venta' => $this->puntoVenta->id,
                    'nombre_razon_social' => "CLIENTE CONTINGENCIA {$i}",
                    'numero_documento' => "8800{$i}00",
                    'codigo_tipo_documento_identidad' => 1,
                    'codigo_metodo_pago' => 1,
                    'items' => [
                        [
                            'descripcion' => "Venta de Insumo Hidráulico {$i}",
                            'cantidad' => 2,
                            'precio_unitario' => 50.00,
                            'monto_descuento' => 0,
                            'codigo_actividad' => '360000',
                            'codigo_producto_sin' => '86330',
                            'codigo_producto_empresa' => 'INS-01',
                            'codigo_unidad_medida' => 58,
                        ],
                    ],
                ]);

            $respFactura->assertStatus(201);
            $this->assertEquals(2, $respFactura->json('data.tipo_emision'));
            $this->assertEquals($eventoId, $respFactura->json('data.id_evento_significativo'));
        }

        // 3. Cerrar Contingencia y Empaquetar en .tar.gz real
        $respCierre = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/facturacion/eventos-significativos/{$eventoId}/cerrar");

        $respCierre->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $paquete = $respCierre->json('data');
        $this->assertNotNull($paquete);
        $this->assertEquals(2, $paquete['cantidad_facturas']);
        $this->assertNotEmpty($paquete['codigo_recepcion_paquete']);
        $this->assertEquals(64, strlen($paquete['hash_archivo'])); // SHA-256 válido

        // Verificar que el archivo .tar.gz existe físicamente en storage
        $this->assertTrue(Storage::disk('local')->exists($paquete['archivo_tar_gz_path']));

        // 4. Validar estado del paquete ante el SIN
        $paqueteId = $paquete['id'];
        $respValidar = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/facturacion/eventos-significativos/paquetes/{$paqueteId}/validar");

        $respValidar->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('facturacion.factura_paquetes', [
            'id' => $paqueteId,
            'cantidad_facturas' => 2,
        ]);
    }

    /**
     * Test 4: Emisión Masiva de Facturas por Lotes
     */
    public function test_emision_masiva_de_facturas_por_lote(): void
    {
        $lote = [
            [
                'codigo_documento_sector' => 13,
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
                'nombre_razon_social' => 'FLORES CONDORI CARLOS',
                'numero_documento' => '120011',
                'codigo_tipo_documento_identidad' => 1,
                'codigo_metodo_pago' => 1,
                'mes' => 'SEPTIEMBRE',
                'gestion' => 2026,
                'numero_medidor' => 'MED-101',
                'consumo_periodo' => 12.0,
                'items' => [
                    [
                        'descripcion' => 'Consumo de Agua Mes',
                        'cantidad' => 1,
                        'precio_unitario' => 25.00,
                        'monto_descuento' => 0,
                        'codigo_actividad' => '360000',
                        'codigo_producto_sin' => '86330',
                        'codigo_producto_empresa' => 'AGUA',
                        'codigo_unidad_medida' => 58,
                    ],
                ],
            ],
            [
                'codigo_documento_sector' => 13,
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
                'nombre_razon_social' => 'QUISPE MAMANI MARIA',
                'numero_documento' => '120022',
                'codigo_tipo_documento_identidad' => 1,
                'codigo_metodo_pago' => 1,
                'mes' => 'SEPTIEMBRE',
                'gestion' => 2026,
                'numero_medidor' => 'MED-102',
                'consumo_periodo' => 18.0,
                'items' => [
                    [
                        'descripcion' => 'Consumo de Agua Mes',
                        'cantidad' => 1,
                        'precio_unitario' => 38.00,
                        'monto_descuento' => 0,
                        'codigo_actividad' => '360000',
                        'codigo_producto_sin' => '86330',
                        'codigo_producto_empresa' => 'AGUA',
                        'codigo_unidad_medida' => 58,
                    ],
                ],
            ],
            [
                'codigo_documento_sector' => 1,
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
                'nombre_razon_social' => 'COMERCIAL PATACAMAYA SRL',
                'numero_documento' => '1029384756',
                'codigo_tipo_documento_identidad' => 5, // NIT
                'codigo_metodo_pago' => 1,
                'items' => [
                    [
                        'descripcion' => 'Venta de Material de Plomería',
                        'cantidad' => 1,
                        'precio_unitario' => 150.00,
                        'monto_descuento' => 0,
                        'codigo_actividad' => '360000',
                        'codigo_producto_sin' => '86330',
                        'codigo_producto_empresa' => 'MAT-01',
                        'codigo_unidad_medida' => 58,
                    ],
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/facturacion/facturas/emision-masiva', [
                'facturas' => $lote,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_procesadas' => 3,
                    'total_emitidas' => 3,
                    'total_errores' => 0,
                    'monto_total_lote' => 213.00,
                ],
            ]);

        $emitidas = $response->json('data.facturas');
        $this->assertCount(3, $emitidas);

        // Verificar que cada una tenga su propio CUF único
        $cufs = array_column($emitidas, 'cuf');
        $this->assertCount(3, array_unique($cufs));
    }

    /**
     * Test 5: Anulación reglamentaria de factura ante el SIN
     */
    public function test_anulacion_reglamentaria_factura_con_motivo_sin(): void
    {
        // Emitir factura para anular
        $respFactura = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/facturacion/facturas', [
                'codigo_documento_sector' => 1,
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
                'nombre_razon_social' => 'CLIENTE PARA ANULACION',
                'numero_documento' => '7788990',
                'codigo_tipo_documento_identidad' => 1,
                'codigo_metodo_pago' => 1,
                'items' => [
                    [
                        'descripcion' => 'Servicio Temporal',
                        'cantidad' => 1,
                        'precio_unitario' => 80.00,
                        'monto_descuento' => 0,
                        'codigo_actividad' => '360000',
                        'codigo_producto_sin' => '86330',
                        'codigo_producto_empresa' => 'SRV-TMP',
                        'codigo_unidad_medida' => 58,
                    ],
                ],
            ]);

        $respFactura->assertStatus(201);
        $facturaId = $respFactura->json('data.id');

        // Anular con motivo 1 (Factura mal emitida)
        $respAnular = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson("/api/facturacion/facturas/{$facturaId}/anular", [
                'codigo_motivo' => 1,
            ]);

        $respAnular->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Factura anulada exitosamente.',
            ]);

        // Verificar en base de datos
        $this->assertDatabaseHas('facturacion.facturas', [
            'id' => $facturaId,
            'estado_factura' => 'ANULADA',
        ]);
    }

    /**
     * Test 6: Reportes oficiales (Libro de Ventas IVA y Exportación CSV oficial)
     */
    public function test_reporte_libro_ventas_iva_y_exportacion_csv(): void
    {
        $mes = Carbon::now()->month;
        $gestion = Carbon::now()->year;

        // 1. Reporte Libro de Ventas en JSON
        $respLibro = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/facturacion/reportes/libro-ventas?mes={$mes}&gestion={$gestion}");

        $respLibro->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'periodo' => ['desde', 'hasta'],
                'resumen' => [
                    'total_registros',
                    'total_facturado',
                    'total_base_debito_fiscal',
                    'debito_fiscal_iva',
                ],
                'data',
            ]);

        // 2. Exportación a CSV oficial para Impuestos Nacionales
        $respCsv = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get("/api/facturacion/reportes/libro-ventas/csv?mes={$mes}&gestion={$gestion}");

        $respCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $respCsv->headers->get('content-type'));
        $content = $respCsv->streamedContent();
        $this->assertStringContainsString('CODIGO DE AUTORIZACION (CUF)', $content);
        $this->assertStringContainsString('NRO;ESPECIFICACION', $content);
    }
}
