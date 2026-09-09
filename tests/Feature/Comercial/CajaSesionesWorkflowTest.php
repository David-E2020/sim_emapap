<?php

declare(strict_types=1);

namespace Tests\Feature\Comercial;

use App\Models\Comercial\CajaMovimiento;
use App\Models\Comercial\CajaSesion;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use App\Services\Comercial\ReporteArqueoCajaPdfService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CajaSesionesWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $cajero;
    protected string $token;
    protected SiatSucursal $sucursal;
    protected SiatPuntoVenta $puntoVenta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajero = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->cajero);

        $this->sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            [
                'nombre' => 'Casa Matriz EMAPAP Test',
                'direccion' => 'Av. Panamericana s/n',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
            ]
        );

        $this->puntoVenta = SiatPuntoVenta::firstOrCreate(
            [
                'id_sucursal' => $this->sucursal->id,
                'codigo_punto_venta' => 99,
            ],
            [
                'nombre' => 'Caja de Pruebas Automatizadas',
                'tipo_punto_venta' => 0,
                'activo' => true,
            ]
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
     * 1. Consulta de cajas disponibles y asignación de cajero habitual.
     */
    public function test_cajas_disponibles_y_asignacion_cajero_habitual(): void
    {
        // Asignar cajero habitual
        $respAsignar = $this->postJson("/api/comercial/cajas/{$this->puntoVenta->id}/asignar-cajero", [
            'id_cajero_defecto' => $this->cajero->id,
        ], $this->authHeaders());

        $respAsignar->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->puntoVenta->refresh();
        $this->assertEquals($this->cajero->id, $this->puntoVenta->id_cajero_defecto);

        // Consultar cajas disponibles
        $respList = $this->getJson('/api/comercial/caja-sesiones/cajas-disponibles', $this->authHeaders());
        $respList->assertStatus(200)
            ->assertJson(['success' => true]);

        $items = collect($respList->json('data'));
        $cajaTest = $items->firstWhere('id', $this->puntoVenta->id);
        $this->assertNotNull($cajaTest);
        $this->assertEquals($this->cajero->id, $cajaTest['id_cajero_defecto']);
    }

    /**
     * 2. Apertura de turno con fondo inicial y verificación de estado activo.
     */
    public function test_apertura_turno_caja_con_fondo_inicial(): void
    {
        $respApertura = $this->postJson('/api/comercial/caja-sesiones/abrir', [
            'id_punto_venta' => $this->puntoVenta->id,
            'monto_apertura' => 150.00,
            'observaciones_apertura' => 'Apertura de turno de prueba automatizada',
        ], $this->authHeaders());

        $respApertura->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'estado' => 'ABIERTA',
                    'monto_apertura' => 150.00,
                ],
            ]);

        $sesionId = $respApertura->json('data.id');
        $sesion = CajaSesion::find($sesionId);
        $this->assertNotNull($sesion);
        $this->assertStringStartsWith('TURNO-', $sesion->numero_sesion);
        $this->assertEquals($this->cajero->id, $sesion->id_cajero);

        // Verificar estado actual vía endpoint
        $respEstado = $this->getJson('/api/comercial/caja-sesiones/estado-actual', $this->authHeaders());
        $respEstado->assertStatus(200)
            ->assertJson([
                'success' => true,
                'tiene_sesion_activa' => true,
                'sesion' => [
                    'id' => $sesionId,
                    'estado' => 'ABIERTA',
                ],
            ]);
    }

    /**
     * 3. Registro de movimientos menores de gaveta (egreso e ingreso extraordinario).
     */
    public function test_movimientos_menores_caja_chica(): void
    {
        // Abrir turno
        $sesion = CajaSesion::create([
            'numero_sesion' => 'TURNO-TEST-001',
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->puntoVenta->id,
            'id_cajero' => $this->cajero->id,
            'fecha_apertura' => Carbon::now(),
            'monto_apertura' => 200.00,
            'total_efectivo_esperado' => 200.00,
            'estado' => 'ABIERTA',
        ]);

        // Registrar egreso (compra de insumos)
        $respEgreso = $this->postJson('/api/comercial/caja-sesiones/movimiento', [
            'id_sesion' => $sesion->id,
            'tipo' => 'EGRESO',
            'monto' => 35.50,
            'concepto' => 'Compra de papel y cinta',
            'beneficiario' => 'Librería Central',
        ], $this->authHeaders());

        $respEgreso->assertStatus(201)->assertJson(['success' => true]);

        // Registrar ingreso extraordinario
        $respIngreso = $this->postJson('/api/comercial/caja-sesiones/movimiento', [
            'id_sesion' => $sesion->id,
            'tipo' => 'INGRESO',
            'monto' => 10.00,
            'concepto' => 'Devolución de cambio',
        ], $this->authHeaders());

        $respIngreso->assertStatus(201)->assertJson(['success' => true]);

        // Verificar recálculo de acumulados
        $sesion->refresh();
        $this->assertEquals(35.50, (float) $sesion->monto_egresos_extra);
        $this->assertEquals(10.00, (float) $sesion->monto_ingresos_extra);
        // Esperado: 200 + 10 - 35.50 = 174.50
        $this->assertEquals(174.50, (float) $sesion->monto_esperado_efectivo);
    }

    /**
     * 4. Arqueo y cierre de caja con conteo de billetes y cálculo de cuadratura.
     */
    public function test_cierre_arqueo_caja_con_desglose_y_cuadratura(): void
    {
        $sesion = CajaSesion::create([
            'numero_sesion' => 'TURNO-TEST-002',
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->puntoVenta->id,
            'id_cajero' => $this->cajero->id,
            'fecha_apertura' => Carbon::now(),
            'monto_apertura' => 230.00,
            'monto_esperado_efectivo' => 230.00,
            'estado' => 'ABIERTA',
        ]);

        // Desglose físico: 1x200 + 1x20 + 1x10 = 230.00 exactos
        $desglose = [
            'b200' => 1,
            'b100' => 0,
            'b50' => 0,
            'b20' => 1,
            'b10' => 1,
            'm5' => 0,
            'm2' => 0,
            'm1' => 0,
            'm050' => 0,
            'm020' => 0,
            'm010' => 0,
        ];

        $respCierre = $this->postJson('/api/comercial/caja-sesiones/cerrar', [
            'id_sesion' => $sesion->id,
            'monto_cierre_declarado' => 230.00,
            'desglose_billetes' => $desglose,
            'observaciones_cierre' => 'Cierre de turno cuadrado sin diferencias',
        ], $this->authHeaders());

        $respCierre->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'estado' => 'CERRADA',
                ],
                'diferencia' => 0.00,
            ]);

        $sesion->refresh();
        $this->assertEquals('CERRADA', $sesion->estado);
        $this->assertEquals(0.00, (float) $sesion->diferencia);
        $this->assertNotNull($sesion->fecha_cierre);
    }

    /**
     * 5. Generación del reporte oficial de arqueo en PDF.
     */
    public function test_generacion_reporte_pdf_arqueo(): void
    {
        $sesion = CajaSesion::create([
            'numero_sesion' => 'TURNO-TEST-PDF',
            'id_sucursal' => $this->sucursal->id,
            'id_punto_venta' => $this->puntoVenta->id,
            'id_cajero' => $this->cajero->id,
            'fecha_apertura' => Carbon::now()->subHours(4),
            'fecha_cierre' => Carbon::now(),
            'monto_apertura' => 150.00,
            'total_ventas_efectivo' => 350.00,
            'total_efectivo_esperado' => 500.00,
            'monto_cierre_fisico' => 500.00,
            'diferencia_sobrante_faltante' => 0.00,
            'estado_cuadratura' => 'CUADRADO',
            'estado' => 'CERRADA',
            'desglose_efectivo' => ['b200' => 2, 'b100' => 1],
        ]);

        /** @var ReporteArqueoCajaPdfService $pdfService */
        $pdfService = app(ReporteArqueoCajaPdfService::class);
        $pdfBinario = $pdfService->generarPdf($sesion);

        $this->assertNotEmpty($pdfBinario);
        // Debe ser un archivo PDF válido (%PDF-)
        $this->assertStringStartsWith('%PDF-', $pdfBinario);

        // Verificar descarga por endpoint
        $respPdf = $this->get("/api/comercial/caja-sesiones/{$sesion->id}/reporte-pdf", $this->authHeaders());
        $respPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $respPdf->headers->get('Content-Type'));
    }
}
