<?php

declare(strict_types=1);

namespace Tests\Feature\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CajaMovimiento;
use App\Models\Comercial\CajaSesion;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\ConvenioCuota;
use App\Models\Comercial\ConvenioPago;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\ReciboCaja;
use App\Models\Comercial\Zona;
use App\Models\Facturacion\Factura;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReporteRecaudacionConsolidadaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $cajero;
    protected string $token;
    protected SiatSucursal $sucursal;
    protected SiatPuntoVenta $caja1;
    protected SiatPuntoVenta $caja2;
    protected Zona $zona;
    protected CategoriaTarifaria $categoria;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajero = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->cajero);

        $this->sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            ['nombre' => 'Matriz Patacamaya Test']
        );

        $this->caja1 = SiatPuntoVenta::firstOrCreate(
            ['id_sucursal' => $this->sucursal->id, 'codigo_punto_venta' => 0],
            ['nombre' => 'Caja 1 - Ventanilla Central', 'tipo_punto_venta' => 0, 'activo' => true]
        );

        $this->caja2 = SiatPuntoVenta::firstOrCreate(
            ['id_sucursal' => $this->sucursal->id, 'codigo_punto_venta' => 1],
            ['nombre' => 'Caja 2 - Ventanilla Cobranzas', 'tipo_punto_venta' => 0, 'activo' => true]
        );

        $this->zona = Zona::firstOrCreate(
            ['codigo' => 'ZONA_REP_TEST'],
            ['nombre' => 'Zona Reportes Test']
        );

        $this->categoria = CategoriaTarifaria::where('activo', true)->first() ?? CategoriaTarifaria::firstOrCreate(
            ['codigo' => 'D'],
            ['nombre' => 'DOMICILIARIA', 'volumen_base' => 6, 'tarifa_minima' => 12.60, 'tarifa_excedente_base' => 2.10, 'tarifa_alcantarillado' => 2, 'activo' => true]
        );
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    /**
     * 1. Consulta consolidada por día con discriminación de rubros y métodos.
     */
    public function test_recaudacion_consolidada_por_dia(): void
    {
        $ahora = Carbon::now();

        // Crear sesión en Caja 1
        $sesion = CajaSesion::create([
            'numero_sesion' => 'TURNO-REP-001',
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->caja1->id,
            'id_cajero' => $this->cajero->id,
            'fecha_apertura' => $ahora,
            'monto_apertura' => 100.00,
            'monto_ventas_efectivo' => 60.00,
            'monto_ventas_qr_banco' => 40.00,
            'monto_esperado_efectivo' => 160.00,
            'monto_cierre_declarado' => 160.00,
            'diferencia' => 0.00,
            'estado' => 'CERRADA',
        ]);

        $abonado = Abonado::create([
            'codigo' => '08001',
            'nombre_completo' => 'CLIENTE TEST REPORTE',
            'numero_documento' => '12345678',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->categoria->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $periodo = PeriodoFacturacion::firstOrCreate(
            ['periodo' => '06/2026'],
            ['gestion' => 2026, 'mes' => 6, 'fecha_inicio_consumo' => '2026-06-01', 'fecha_fin_consumo' => '2026-06-30', 'fecha_vencimiento_pago' => '2026-07-15', 'estado' => 'CERRADO']
        );

        // Factura Efectivo (metodo 1)
        $facturaEf = Factura::create([
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->caja1->id,
            'id_sesion_caja' => $sesion->id,
            'numero_factura' => 9991,
            'cuf' => 'CUF_TEST_EF_' . uniqid(),
            'fecha_emision' => $ahora,
            'codigo_metodo_pago' => 1,
            'monto_total' => 60.00,
            'monto_total_sujeto_iva' => 50.00,
            'nombre_razon_social' => $abonado->nombre_completo,
            'numero_documento' => $abonado->numero_documento,
            'leyenda' => 'Ley N° 453: Servicios Básicos',
            'usuario_emision' => 'admin',
            'estado_factura' => 'VALIDADA',
        ]);

        $lecturaEf = LecturaMensual::create([
            'id_periodo' => $periodo->id,
            'id_abonado' => $abonado->id,
            'id_cajero' => $this->cajero->id,
            'id_sesion_caja' => $sesion->id,
            'id_factura' => $facturaEf->id,
            'fecha_pago' => $ahora,
            'estado_pago' => 'PAGADO',
            'monto_agua' => 50.00,
            'monto_alcantarillado' => 10.00,
            'total_facturado' => 60.00,
        ]);

        $abonado2 = Abonado::create([
            'codigo' => '08002',
            'nombre_completo' => 'CLIENTE TEST DOS',
            'numero_documento' => '87654321',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->categoria->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        // Factura QR (metodo 7)
        $facturaQr = Factura::create([
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->caja1->id,
            'id_sesion_caja' => $sesion->id,
            'numero_factura' => 9992,
            'cuf' => 'CUF_TEST_QR_' . uniqid(),
            'fecha_emision' => $ahora,
            'codigo_metodo_pago' => 7,
            'monto_total' => 40.00,
            'monto_total_sujeto_iva' => 35.00,
            'nombre_razon_social' => $abonado2->nombre_completo,
            'numero_documento' => $abonado2->numero_documento,
            'leyenda' => 'Ley N° 453: Servicios Básicos',
            'usuario_emision' => 'admin',
            'estado_factura' => 'VALIDADA',
        ]);

        $lecturaQr = LecturaMensual::create([
            'id_periodo' => $periodo->id,
            'id_abonado' => $abonado2->id,
            'id_cajero' => $this->cajero->id,
            'id_sesion_caja' => $sesion->id,
            'id_factura' => $facturaQr->id,
            'fecha_pago' => $ahora,
            'estado_pago' => 'PAGADO',
            'monto_agua' => 35.00,
            'monto_alcantarillado' => 5.00,
            'total_facturado' => 40.00,
        ]);

        // Consultar reporte consolidado para "hoy"
        $resp = $this->getJson('/api/comercial/reportes/recaudacion-consolidada?periodo_tipo=hoy', $this->authHeaders());

        $resp->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $resp->json('data');
        $this->assertEquals('hoy', $data['periodo']['tipo']);
        $this->assertGreaterThanOrEqual(100.00, (float) $data['metricas']['total_recaudado']);
        $this->assertGreaterThanOrEqual(60.00, (float) $data['metricas']['total_efectivo']);
        $this->assertGreaterThanOrEqual(40.00, (float) $data['metricas']['total_qr_banco']);

        // Validar desglose por rubro
        $rubros = collect($data['por_rubro']);
        $rubroAgua = $rubros->firstWhere('nombre', 'Servicio de Agua Potable');
        $this->assertNotNull($rubroAgua);
        $this->assertGreaterThanOrEqual(85.00, (float) $rubroAgua['total']); // 50 + 35
    }

    /**
     * 2. Consulta con filtro de fechas por rango personalizado (semana / mes).
     */
    public function test_recaudacion_consolidada_por_rango_personalizado(): void
    {
        $fechaInicio = Carbon::now()->subDays(5)->toDateString();
        $fechaFin = Carbon::now()->toDateString();

        $resp = $this->getJson("/api/comercial/reportes/recaudacion-consolidada?periodo_tipo=personalizado&fecha_inicio={$fechaInicio}&fecha_fin={$fechaFin}", $this->authHeaders());

        $resp->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $resp->json('data');
        $this->assertArrayHasKey('metricas', $data);
        $this->assertArrayHasKey('por_rubro', $data);
        $this->assertArrayHasKey('por_caja', $data);
        $this->assertArrayHasKey('por_cajero', $data);
        $this->assertArrayHasKey('turnos', $data);
    }

    /**
     * 3. Descarga oficial en formato PDF.
     */
    public function test_descargar_pdf_recaudacion_consolidada(): void
    {
        $resp = $this->get('/api/comercial/reportes/recaudacion-consolidada/pdf?periodo_tipo=hoy', $this->authHeaders());

        $resp->assertStatus(200);
        $this->assertEquals('application/pdf', $resp->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $resp->getContent());
    }

    /**
     * 4. Exportación en formato CSV delimitado.
     */
    public function test_exportar_csv_recaudacion_consolidada(): void
    {
        $resp = $this->get('/api/comercial/reportes/recaudacion-consolidada/csv?periodo_tipo=hoy', $this->authHeaders());

        $resp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $resp->headers->get('Content-Type'));
    }
}
