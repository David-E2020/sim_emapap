<?php

declare(strict_types=1);

namespace Tests\Feature\Contabilidad;

use App\Models\Comercial\CajaSesion;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Contabilidad\Comprobante;
use App\Models\Contabilidad\PlanCuenta;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class IntegracionContabilidadTest extends TestCase
{
    use DatabaseTransactions;

    protected User $usuario;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->usuario = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->usuario);
    }

    /**
     * Test de Integración Caja -> Contabilidad:
     * Contabilización automática de cierre de caja generando Comprobante de Ingreso (CI).
     */
    public function test_interfaz_automatica_contabiliza_sesion_caja_cerrada_generando_comprobante_ingreso_balanceado(): void
    {
        $sucursal = SiatSucursal::firstOrCreate(['codigo_sucursal' => 0], [
            'nombre' => 'EMAPAP Central',
            'direccion' => 'Patacamaya',
            'municipio' => 'Patacamaya',
            'departamento' => 'La Paz',
        ]);

        $puntoVenta = SiatPuntoVenta::firstOrCreate(['codigo_punto_venta' => 1, 'id_sucursal' => $sucursal->id], [
            'nombre' => 'Caja Central 1',
            'tipo_punto_venta' => 'VENTANILLA_COBRANZA',
            'estado' => 'ACTIVO',
        ]);

        $sesion = CajaSesion::create([
            'numero_sesion' => 'SES-TEST-001',
            'id_sucursal' => $sucursal->id,
            'id_punto_venta' => $puntoVenta->id,
            'id_cajero' => $this->usuario->id,
            'fecha_apertura' => Carbon::now()->subHours(4),
            'monto_apertura' => 200.00,
            'monto_ventas_efectivo' => 1000.50,
            'monto_ventas_qr_banco' => 250.00,
            'fecha_cierre' => Carbon::now(),
            'monto_cierre_declarado' => 1200.50,
            'monto_esperado_efectivo' => 1200.50,
            'diferencia' => 0.00,
            'estado' => 'CERRADA',
            '_transaccion' => 'CIERRE_ARQUEO',
            '_usuario_creacion' => $this->usuario->id,
            '_fecha_creacion' => now(),
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/interfases/contabilizar-caja', [
                'caja_sesion_id' => $sesion->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $comprobanteData = $response->json('data');
        $this->assertNotNull($comprobanteData);
        $this->assertEquals('INGRESO', $comprobanteData['tipo']);
        $this->assertEquals(1250.50, (float) $comprobanteData['total_debe']);
        $this->assertEquals(1250.50, (float) $comprobanteData['total_haber']);
        $this->assertEquals(0.00, (float) $comprobanteData['diferencia']);

        // Verificar enlace en caja_sesiones
        $sesion->refresh();
        $this->assertEquals($comprobanteData['id'], $sesion->id_comprobante);

        // Verificar detalles contables
        $compDb = Comprobante::with('detalles.cuenta')->find($comprobanteData['id']);
        $this->assertNotNull($compDb);
        $this->assertCount(3, $compDb->detalles);

        // Debe haber líneas para efectivo, banco digital y contrapartida cuentas por cobrar
        $codigos = $compDb->detalles->pluck('cuenta.codigo')->toArray();
        $this->assertContains('1.1.1.01.001', $codigos); // Caja
        $this->assertContains('1.1.1.02.002', $codigos); // Banco QR
        $this->assertContains('1.1.2.01.001', $codigos); // Cuentas por cobrar
    }

    /**
     * Test de Integración Facturación / Lecturas -> Contabilidad:
     * Devengamiento mensual por venta de agua potable y alcantarillado.
     */
    public function test_interfaz_automatica_contabiliza_lecturas_y_facturacion_agua(): void
    {
        $periodoId = DB::table('comercial.lecturas_mensuales')
            ->where('_estado', 'ACTIVO')
            ->value('id_periodo') ?? 49;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/interfases/contabilizar-lecturas', [
                'id_periodo' => $periodoId,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $comprobanteData = $response->json('data');
        $this->assertNotNull($comprobanteData);
        $this->assertEquals('DIARIO', $comprobanteData['tipo']);
        $this->assertEquals((float) $comprobanteData['total_debe'], (float) $comprobanteData['total_haber']);
        $this->assertEquals(0.00, (float) $comprobanteData['diferencia']);
        $this->assertGreaterThan(0, (float) $comprobanteData['total_debe']);

        // Verificar partida doble en base de datos
        $compDb = Comprobante::with('detalles.cuenta')->find($comprobanteData['id']);
        $this->assertNotNull($compDb);

        $codigos = $compDb->detalles->pluck('cuenta.codigo')->toArray();
        $this->assertContains('1.1.2.01.001', $codigos); // Cuentas por Cobrar (Debe)
        $this->assertContains('2.1.2.01', $codigos);     // Débito Fiscal IVA (Haber)
        $this->assertContains('5.1.1.01', $codigos);     // Ingreso Agua (Haber)
    }

    /**
     * Test de Integración RRHH -> Contabilidad:
     * Devengamiento de planilla de sueldos y aportes patronales (CNS, Gestora, etc.).
     */
    public function test_interfaz_automatica_contabiliza_planilla_sueldos_rrhh(): void
    {
        $planillaId = DB::table('rrhh.detalles_planillas')->value('id_planilla_consolidada') ?? 23;

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/interfases/contabilizar-planilla-sueldos', [
                'id_planilla' => $planillaId,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $comprobanteData = $response->json('data');
        $this->assertNotNull($comprobanteData);
        $this->assertEquals('DIARIO', $comprobanteData['tipo']);
        $this->assertEquals((float) $comprobanteData['total_debe'], (float) $comprobanteData['total_haber']);
        $this->assertEquals(0.00, (float) $comprobanteData['diferencia']);
        $this->assertGreaterThan(0, (float) $comprobanteData['total_debe']);

        // Verificar cuentas de gastos y pasivos de ley laboral boliviana
        $compDb = Comprobante::with('detalles.cuenta')->find($comprobanteData['id']);
        $this->assertNotNull($compDb);

        $codigos = $compDb->detalles->pluck('cuenta.codigo')->toArray();
        $this->assertContains('6.1.1.01', $codigos); // Gasto Sueldos
        $this->assertContains('6.1.1.05', $codigos); // Gasto Patronal CNS
        $this->assertContains('6.1.1.06', $codigos); // Gasto Riesgo Profesional
        $this->assertContains('2.1.1.01', $codigos); // Sueldos por Pagar
        $this->assertContains('2.1.2.02', $codigos); // Retenciones Gestora Laboral
        $this->assertContains('2.1.2.03', $codigos); // Aportes Patronales por Pagar
    }

    /**
     * Test de Reportes Financieros SAFCO:
     * Libro Diario, Libro Mayor, Balance de Comprobación, Balance General y Estado de Rendimiento.
     */
    public function test_reportes_financieros_safco_libro_diario_mayor_y_estados_financieros(): void
    {
        // 1. Crear un comprobante de prueba para asegurar que haya movimientos en el período
        $ctaCaja = PlanCuenta::where('codigo', '1.1.1.01.001')->firstOrFail();
        $ctaIngreso = PlanCuenta::where('codigo', '5.1.1.01')->firstOrFail();

        $payload = [
            'tipo' => 'CI',
            'fecha' => '2026-03-15',
            'beneficiario' => 'Test Reportes',
            'glosa' => 'Cobro para pruebas de estados financieros',
            'detalles' => [
                ['plan_cuenta_id' => $ctaCaja->id, 'glosa' => 'Caja', 'debe' => 500.00, 'haber' => 0.00],
                ['plan_cuenta_id' => $ctaIngreso->id, 'glosa' => 'Ingreso Agua', 'debe' => 0.00, 'haber' => 500.00],
            ],
        ];

        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/comprobantes', $payload);

        // 2. Libro Diario
        $resDiario = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/reportes/libro-diario?fecha_inicio=2026-01-01&fecha_fin=2026-12-31');

        $resDiario->assertStatus(200)
            ->assertJsonStructure(['success', 'data']);

        // 3. Libro Mayor de Caja Efectivo
        $resMayor = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/reportes/libro-mayor?plan_cuenta_id=' . $ctaCaja->id . '&fecha_inicio=2026-01-01&fecha_fin=2026-12-31');

        $resMayor->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['cuenta', 'movimientos', 'total_debe', 'total_haber', 'saldo_final']]);

        // 4. Balance de Comprobación (Sumas y Saldos)
        $resSumasSaldos = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/reportes/balance-comprobacion?gestion=2026');

        $resSumasSaldos->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['cuentas', 'totales']]);

        $totales = $resSumasSaldos->json('data.totales');
        $this->assertEquals((float) $totales['total_debe'], (float) $totales['total_haber']);
        $this->assertEquals((float) $totales['total_deudor'], (float) $totales['total_acreedor']);

        // 5. Balance General Clasificado
        $resBalanceGen = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/reportes/balance-general?gestion=2026');

        $resBalanceGen->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['cuentas_activo', 'cuentas_pasivo', 'cuentas_patrimonio', 'total_activo', 'total_pasivo', 'total_patrimonio', 'balance_cuadrado']]);

        // 6. Estado de Rendimiento (Pérdidas y Ganancias)
        $resResultados = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/reportes/estado-resultados?gestion=2026');

        $resResultados->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['cuentas_ingreso', 'cuentas_gasto', 'total_ingresos', 'total_gastos', 'resultado_neto']]);
    }
}
