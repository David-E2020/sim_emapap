<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\Facturacion\Factura;
use App\Models\User;
use App\Services\Facturacion\CufService;
use App\Services\Facturacion\XmlFacturaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FacturacionIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'admin@emapa.gob.bo')->first() ?: User::first();
        if ($this->user) {
            $rolAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador General', 'guard_name' => 'api']);
            if (!$this->user->hasRole('Administrador General')) {
                $this->user->assignRole($rolAdmin);
            }
            $this->token = JWTAuth::fromUser($this->user);
        }
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_cuf_service_calcula_modulo_11_y_cuf_correctamente(): void
    {
        $cufService = new CufService();

        // 1. Prueba de Módulo 11
        $cadenaEntrada = "123456789";
        $resultadoMod11 = $cufService->calcularModulo11($cadenaEntrada);
        $this->assertGreaterThan(strlen($cadenaEntrada), strlen($resultadoMod11));

        // 2. Prueba de conversión Hexadecimal
        $hex = $cufService->dec2hex("1234567890123456789012345678901234567890");
        $this->assertEquals("3A0C92075C0DBF3B8ACBC5F96CE3F0AD2", $hex);

        // 3. Prueba de Generación de CUF
        $cuf = $cufService->generarCuf(
            "123456789",
            Carbon::now(),
            0, // Casa Matriz
            1, // Modalidad Electrónica
            1, // En Línea
            1, // Con crédito fiscal
            1, // Compra Venta
            1, // Factura N° 1
            0, // Punto de venta 0
            "A1B2C3D4E5F6" // Código de control
        );

        $this->assertNotEmpty($cuf);
        $this->assertStringEndsWith("A1B2C3D4E5F6", $cuf);
    }

    public function test_catalogo_y_productos_siat_endpoints_responden_correctamente(): void
    {
        $responseCatalogos = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/siat/catalogos');

        $responseCatalogos->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    'tipos_documento',
                    'metodos_pago',
                    'motivos_anulacion',
                    'unidades_medida',
                ],
            ]);

        $responseProductos = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/siat/productos');

        $responseProductos->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_emision_de_factura_agua_y_alcantarillado_emapap_patacamaya(): void
    {
        $payload = [
            'id_sucursal' => 0,
            'id_punto_venta' => 0,
            'codigo_tipo_documento_identidad' => 1,
            'numero_documento' => '659730',
            'nombre_razon_social' => 'CALLE DE SAJAMA ISIDORA',
            'correo_electronico' => 'abonado@emapap.bo',
            'codigo_metodo_pago' => 1, // Efectivo
            'monto_descuento' => 0.00,
            'items' => [
                [
                    'codigo_producto_empresa' => 'AGUA-DOM-01',
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86311',
                    'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA DOMICILIARIA (MÍNIMO 6 M³)',
                    'cantidad' => 1,
                    'precio_unitario' => 12.60,
                    'monto_descuento' => 0,
                ],
                [
                    'codigo_producto_empresa' => 'ALC-DOM-01',
                    'codigo_actividad' => '370000',
                    'codigo_producto_sin' => '86312',
                    'descripcion' => 'SERVICIO DE ALCANTARILLADO SANITARIO - TARIFA BÁSICA DOMICILIARIA',
                    'cantidad' => 1,
                    'precio_unitario' => 2.00,
                    'monto_descuento' => 0,
                ],
                [
                    'codigo_producto_empresa' => 'SRV-02-REC',
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86313',
                    'descripcion' => 'RECONEXIÓN DE SERVICIO DE AGUA POTABLE',
                    'cantidad' => 1,
                    'precio_unitario' => 50.00,
                    'monto_descuento' => 0,
                ],
            ],
        ];

        // Total = 12.60 + 2.00 + 50.00 = 64.60
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/facturas', $payload);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $facturaId = $response->json('data.id');
        $this->assertNotNull($facturaId);

        $factura = Factura::with('detalles')->find($facturaId);
        $this->assertNotNull($factura);
        $this->assertEquals(64.60, (float) $factura->monto_total);
        $this->assertEquals(64.60, (float) $factura->monto_total_sujeto_iva);
        $this->assertEquals(0.00, (float) $factura->monto_descuento);
        $this->assertCount(3, $factura->detalles);

        // Validar que se construyó el XML oficial con servicios de agua
        $xmlService = new XmlFacturaService();
        $xml = $xmlService->construirXml($factura);
        $tagEsperado = (int) $factura->codigo_modalidad === 2 ? '<facturaComputarizadaCompraVenta' : '<facturaElectronicaCompraVenta';
        $this->assertStringContainsString($tagEsperado, $xml);
        $this->assertStringContainsString('<cuf>' . $factura->cuf . '</cuf>', $xml);
        $this->assertStringContainsString('CONSUMO AGUA POTABLE', $xml);
        $this->assertStringContainsString('ALCANTARILLADO SANITARIO', $xml);

        // Validar endpoints de descarga de PDF y XML
        $responsePdf = $this->get("/api/facturacion/publico/facturas/{$facturaId}/pdf");
        $responsePdf->assertStatus(200);

        $responseXml = $this->get("/api/facturacion/publico/facturas/{$facturaId}/xml");
        $responseXml->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml');

        // Probar anulación de factura
        $responseAnulacion = $this->withHeaders($this->authHeaders())
            ->postJson("/api/facturacion/facturas/{$facturaId}/anular", [
                'codigo_motivo' => 1,
            ]);

        $responseAnulacion->assertStatus(200)
            ->assertJson(['success' => true]);

        $factura->refresh();
        $this->assertEquals('ANULADA', $factura->estado_factura);
    }

    public function test_bandeja_de_facturas_retorna_paginacion_y_filtros(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/facturas?per_page=5');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data',
                'total',
                'current_page',
                'last_page',
            ]);
    }

    public function test_eventos_significativos_flujo_de_apertura_y_listado(): void
    {
        $payload = [
            'codigo_evento' => 1, // Corte de energía eléctrica
            'descripcion' => 'Corte imprevisto de energía eléctrica en oficina central Patacamaya',
            'id_sucursal' => 0,
            'id_punto_venta' => 0,
            'cafc' => 'CAFC-PAT-2026-001',
        ];

        $responseCreate = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/eventos-significativos', $payload);

        $responseCreate->assertStatus(201)
            ->assertJson(['success' => true]);

        $responseList = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/eventos-significativos');

        $responseList->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
