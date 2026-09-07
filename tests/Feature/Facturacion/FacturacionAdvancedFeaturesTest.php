<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Mail\Facturacion\FacturaEmitidaMail;
use App\Models\Facturacion\EventoSignificativo;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\FacturaDetalle;
use App\Models\Facturacion\FacturaPaquete;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class FacturacionAdvancedFeaturesTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_renovar_cufd_command_ejecuta_y_persiste_en_base_de_datos(): void
    {
        $conteoAntes = SiatCufd::count();

        $this->artisan('facturacion:renovar-cufd')
            ->assertExitCode(0);

        $conteoDespues = SiatCufd::count();
        $this->assertGreaterThan($conteoAntes, $conteoDespues);

        $ultimoCufd = SiatCufd::latest('id')->first();
        $this->assertNotNull($ultimoCufd);
        $this->assertNotEmpty($ultimoCufd->codigo);
        $this->assertNotEmpty($ultimoCufd->codigo_control);
    }

    public function test_sincronizar_catalogos_command_actualiza_catalogos(): void
    {
        $this->artisan('facturacion:sincronizar-catalogos')
            ->assertExitCode(0);

        $this->assertDatabaseHas('facturacion.catalogos_sin', [
            'tipo_catalogo' => 'METODO_PAGO',
            'codigo' => '1',
        ]);

        $this->assertDatabaseHas('facturacion.catalogos_sin', [
            'tipo_catalogo' => 'UNIDAD_MEDIDA',
            'codigo' => '65', // Metro cúbico
        ]);
    }

    public function test_envio_factura_por_correo_adjunta_pdf_y_xml(): void
    {
        Mail::fake();

        // 1. Emitir factura de prueba para EMAPAP Patacamaya
        $payload = [
            'id_sucursal' => 0,
            'id_punto_venta' => 0,
            'codigo_tipo_documento_identidad' => 1,
            'numero_documento' => '2110335',
            'nombre_razon_social' => 'SAJAMA MOLLO EMILIO',
            'correo_electronico' => 'emilio.sajama@patacamaya.bo',
            'codigo_metodo_pago' => 1,
            'items' => [
                [
                    'codigo_producto_empresa' => 'AGUA-DOM-01',
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86311',
                    'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA DOMICILIARIA',
                    'cantidad' => 1,
                    'precio_unitario' => 12.60,
                ],
            ],
        ];

        $resEmision = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/facturas', $payload);

        $resEmision->assertStatus(201);
        $facturaId = $resEmision->json('data.id');

        // 2. Reenviar por correo mediante el endpoint
        $resMail = $this->withHeaders($this->authHeaders())
            ->postJson("/api/facturacion/facturas/{$facturaId}/enviar-correo", [
                'correo_electronico' => 'abonado.test@emapap.bo',
            ]);

        $resMail->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Verificar que Mail fue despachado
        Mail::assertSent(FacturaEmitidaMail::class, function ($mail) {
            return $mail->hasTo('abonado.test@emapap.bo') &&
                   $mail->factura !== null;
        });
    }

    public function test_empaquetado_tar_gz_real_y_cierre_de_contingencia(): void
    {
        $sucursal = SiatSucursal::first() ?? SiatSucursal::create([
            'codigo_sucursal' => 0,
            'nombre' => 'Matriz Patacamaya',
            'direccion' => 'Plaza 15 de Agosto',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'TEST',
            '_usuario_creacion' => 1,
        ]);

        // 1. Iniciar contingencia
        $resEvento = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/eventos-significativos', [
                'codigo_evento' => 1,
                'descripcion' => 'Corte de energía en planta de bombeo Patacamaya',
                'id_sucursal' => $sucursal->id,
                'cafc' => 'CAFC-PATACAMAYA-2026',
            ]);

        $resEvento->assertStatus(201);
        $eventoId = $resEvento->json('data.id');

        $cufd = SiatCufd::first() ?? SiatCufd::create([
            'id_sucursal' => $sucursal->id,
            'codigo' => 'CUFD_TEST',
            'codigo_control' => 'CONTROL123',
            'direccion' => 'Patacamaya',
            'fecha_vigencia' => Carbon::now()->addHours(24),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'TEST',
            '_usuario_creacion' => 1,
        ]);

        // 2. Crear una factura fuera de línea asignada al evento
        $factura = Factura::create([
            'id_sucursal' => $sucursal->id,
            'id_cufd' => $cufd->id,
            'id_evento_significativo' => $eventoId,
            'numero_factura' => 99999,
            'cuf' => 'CUF_CONTINGENCIA_TEST_' . bin2hex(random_bytes(8)),
            'cufd' => $cufd->codigo,
            'codigo_control' => $cufd->codigo_control ?? 'CONTROL123',
            'leyenda' => 'Ley N° 453: El proveedor debe brindar atención sin discriminación.',
            'usuario_emision' => 'ADMIN_TEST',
            'fecha_emision' => Carbon::now(),
            'codigo_tipo_documento_identidad' => 1,
            'numero_documento' => '1234567',
            'nombre_razon_social' => 'SOCIO CONTINGENCIA',
            'monto_total' => 15.00,
            'monto_total_sujeto_iva' => 15.00,
            'tipo_emision' => 2, // 2 = Fuera de línea
            'estado_factura' => 'CONTINGENCIA',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'TEST',
            '_usuario_creacion' => 1,
        ]);

        FacturaDetalle::create([
            'id_factura' => $factura->id,
            'codigo_actividad' => '360000',
            'codigo_producto_sin' => '86311',
            'codigo_producto_empresa' => 'AGUA-DOM-01',
            'descripcion' => 'CONSUMO AGUA POTABLE',
            'cantidad' => 1,
            'codigo_unidad_medida' => 58,
            'precio_unitario' => 15.00,
            'subtotal' => 15.00,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'TEST',
            '_usuario_creacion' => 1,
        ]);

        // 3. Cerrar contingencia y verificar creación de paquete .tar.gz real
        $resCerrar = $this->withHeaders($this->authHeaders())
            ->postJson("/api/facturacion/eventos-significativos/{$eventoId}/cerrar");

        $resCerrar->assertStatus(200)
            ->assertJson(['success' => true]);

        $paquete = FacturaPaquete::where('id_evento_significativo', $eventoId)->first();
        $this->assertNotNull($paquete);
        $this->assertEquals(1, $paquete->cantidad_facturas);
        $this->assertTrue(Storage::disk('local')->exists($paquete->archivo_tar_gz_path));

        // 4. Validar integridad del archivo .tar.gz con PharData de PHP
        $fullPath = Storage::disk('local')->path($paquete->archivo_tar_gz_path);
        $phar = new \PharData($fullPath);
        $this->assertTrue(isset($phar["factura_{$factura->numero_factura}.xml"]));

        // 5. Validar endpoint de validación de paquete
        $resValidar = $this->withHeaders($this->authHeaders())
            ->postJson("/api/facturacion/eventos-significativos/paquetes/{$paquete->id}/validar");

        $resValidar->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_reporte_libro_ventas_iva_calculos_y_exportacion_csv_oficial(): void
    {
        $hoy = Carbon::now()->format('Y-m-d');

        // 1. Consultar API de métricas del Libro de Ventas
        $resLibro = $this->withHeaders($this->authHeaders())
            ->getJson("/api/facturacion/reportes/libro-ventas?fecha_desde={$hoy}&fecha_hasta={$hoy}");

        $resLibro->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'periodo',
                'resumen' => [
                    'total_registros',
                    'cantidad_validas',
                    'cantidad_anuladas',
                    'total_facturado',
                    'total_base_debito_fiscal',
                    'debito_fiscal_iva',
                ],
                'data',
            ]);

        // 2. Consultar exportación oficial en CSV
        $resCsv = $this->withHeaders($this->authHeaders())
            ->get("/api/facturacion/reportes/libro-ventas/csv?fecha_desde={$hoy}&fecha_hasta={$hoy}");

        $resCsv->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csvContent = $resCsv->streamedContent();
        $this->assertStringContainsString('CODIGO DE AUTORIZACION (CUF)', $csvContent);
        $this->assertStringContainsString('DEBITO FISCAL (13%)', $csvContent);
        $this->assertStringContainsString('BASE PARA DEBITO FISCAL', $csvContent);

        // 3. Consultar ventas mensuales para dashboard
        $resMensual = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/reportes/ventas-mensuales');

        $resMensual->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'gestion',
                'data' => [
                    '*' => ['mes', 'nombre_mes', 'total_facturas', 'total_monto'],
                ],
            ]);
    }

    public function test_descargar_pdf_formato_rollo_80mm(): void
    {
        // 1. Emitir factura
        $payload = [
            'id_sucursal' => 0,
            'id_punto_venta' => 0,
            'codigo_tipo_documento_identidad' => 1,
            'numero_documento' => '2110335',
            'nombre_razon_social' => 'MAMANI TICONA JUAN',
            'correo_electronico' => 'juan.mamani@patacamaya.bo',
            'codigo_metodo_pago' => 1,
            'items' => [
                [
                    'codigo_producto_empresa' => 'AGUA-DOM-01',
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86311',
                    'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA DOMICILIARIA',
                    'cantidad' => 1,
                    'precio_unitario' => 25.50,
                ],
            ],
        ];

        $resEmision = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/facturas', $payload);

        $resEmision->assertStatus(201);
        $facturaId = $resEmision->json('data.id');

        // 2. Descargar en formato rollo 80mm
        $resRollo = $this->withHeaders($this->authHeaders())
            ->get("/api/facturacion/facturas/{$facturaId}/pdf?formato=rollo");

        $resRollo->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');

        $disposition = $resRollo->headers->get('Content-Disposition');
        $this->assertStringContainsString('rollo', strtolower($disposition));
    }

    public function test_verificar_estado_factura_en_linea_con_siat(): void
    {
        $payload = [
            'id_sucursal' => 0,
            'id_punto_venta' => 0,
            'codigo_tipo_documento_identidad' => 1,
            'numero_documento' => '4982341',
            'nombre_razon_social' => 'QUISPE CONDORI MARIA',
            'codigo_metodo_pago' => 1,
            'items' => [
                [
                    'codigo_producto_empresa' => 'AGUA-COM-01',
                    'codigo_actividad' => '360000',
                    'codigo_producto_sin' => '86311',
                    'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA COMERCIAL',
                    'cantidad' => 1,
                    'precio_unitario' => 45.00,
                ],
            ],
        ];

        $resEmision = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/facturas', $payload);

        $resEmision->assertStatus(201);
        $facturaId = $resEmision->json('data.id');

        // Verificar estado ante el SIN
        $resVerif = $this->withHeaders($this->authHeaders())
            ->getJson("/api/facturacion/facturas/{$facturaId}/verificar-estado-sin");

        $resVerif->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'estado_local',
                'siat',
            ]);
    }

    public function test_sincronizar_hora_servidor_con_siat(): void
    {
        $resSync = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/siat/sincronizar-hora');

        $resSync->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'fecha_hora_sin',
                'fecha_hora_local',
                'diferencia_segundos',
            ]);
    }
}
