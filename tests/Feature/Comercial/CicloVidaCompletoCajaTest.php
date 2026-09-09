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
use App\Models\Comercial\Zona;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use App\Services\Comercial\CobranzaAguaService;
use App\Services\Comercial\LecturacionService;
use App\Services\Comercial\ReporteArqueoCajaPdfService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CicloVidaCompletoCajaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $cajero;
    protected string $token;
    protected SiatSucursal $sucursal;
    protected SiatPuntoVenta $cajaVentanilla;
    protected Zona $zona;
    protected CategoriaTarifaria $categoriaDom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajero = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->cajero);

        $this->sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            [
                'nombre' => 'EMAPAP Central Patacamaya',
                'direccion' => 'Av. Panamericana s/n',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
            ]
        );

        $this->cajaVentanilla = SiatPuntoVenta::firstOrCreate(
            [
                'id_sucursal' => $this->sucursal->id,
                'codigo_punto_venta' => 2,
            ],
            [
                'nombre' => 'Caja 2 - Ventanilla Norte Cobranzas',
                'tipo_punto_venta' => 0,
                'activo' => true,
            ]
        );

        $this->zona = Zona::firstOrCreate(
            ['codigo' => 'ZONA_CENTRAL_TEST'],
            ['nombre' => 'Zona Central Test']
        );

        $this->categoriaDom = CategoriaTarifaria::where('activo', true)->first() ?? CategoriaTarifaria::firstOrCreate(
            ['codigo' => 'D'],
            [
                'nombre' => 'DOMICILIARIA',
                'volumen_base' => 6.00,
                'tarifa_minima' => 12.60,
                'tarifa_excedente_base' => 2.10,
                'tarifa_alcantarillado' => 2.00,
                'aplica_ley_1886' => true,
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
     * Test del ciclo de vida integral:
     * Asignación -> Apertura -> Búsqueda -> Cobro Efectivo -> Cobro QR -> Cobro Convenio -> Movimiento Caja Chica -> Arqueo/Cierre -> PDF.
     */
    public function test_ciclo_de_vida_completo_cobranza_y_arqueo(): void
    {
        // -------------------------------------------------------------
        // FASE 1: ASIGNACIÓN DE CAJERO HABITUAL A LA CAJA
        // -------------------------------------------------------------
        $respAsignar = $this->postJson("/api/comercial/cajas/{$this->cajaVentanilla->id}/asignar-cajero", [
            'id_cajero' => $this->cajero->id,
        ], $this->authHeaders());

        $respAsignar->assertStatus(200)->assertJson(['success' => true]);
        $this->cajaVentanilla->refresh();
        $this->assertEquals($this->cajero->id, $this->cajaVentanilla->id_cajero_defecto);

        // -------------------------------------------------------------
        // FASE 2: APERTURA DE TURNO DE CAJA (CON FONDO DE BS. 150.00)
        // -------------------------------------------------------------
        $respApertura = $this->postJson('/api/comercial/caja-sesiones/abrir', [
            'id_punto_venta' => $this->cajaVentanilla->id,
            'monto_apertura' => 150.00,
            'observaciones_apertura' => 'Inicio de jornada - Ventanilla 2',
        ], $this->authHeaders());

        $respApertura->assertStatus(201)->assertJson(['success' => true]);
        $sesionId = $respApertura->json('data.id');
        $this->assertNotNull($sesionId);

        $sesion = CajaSesion::findOrFail($sesionId);
        $this->assertEquals('ABIERTA', $sesion->estado);
        $this->assertEquals(150.00, (float) $sesion->monto_apertura);
        $this->assertEquals(150.00, (float) $sesion->monto_esperado_efectivo);

        // -------------------------------------------------------------
        // FASE 3: BÚSQUEDA DE ABONADO Y CONSULTA DE ESTADO DE CUENTA
        // -------------------------------------------------------------
        $abonado1 = Abonado::create([
            'codigo' => '07001',
            'nombre_completo' => 'ALEJANDRO MAMANI HUANCA',
            'numero_documento' => '4892011',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->categoriaDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $periodo = PeriodoFacturacion::firstOrCreate(
            ['periodo' => '05/2026'],
            [
                'gestion' => 2026,
                'mes' => 5,
                'fecha_inicio_consumo' => '2026-05-01',
                'fecha_fin_consumo' => '2026-05-31',
                'fecha_vencimiento_pago' => '2026-06-15',
                'estado' => 'CERRADO',
            ]
        );

        $lectura1 = LecturaMensual::create([
            'id_periodo' => $periodo->id,
            'id_abonado' => $abonado1->id,
            'lectura_anterior' => 100,
            'lectura_actual' => 110,
            'consumo_m3' => 10,
            'monto_agua' => 21.00,
            'monto_alcantarillado' => 2.00,
            'monto_descuento_ley1886' => 0.00,
            'total_facturado' => 23.00,
            'estado_pago' => 'PENDIENTE',
            '_estado' => 'ACTIVO',
        ]);

        $abonado1->update(['saldo_deuda' => 23.00, 'meses_mora' => 1]);

        // Consulta predictiva por código
        $respBuscar = $this->getJson('/api/comercial/caja/buscar-abonados?q=07001&tipo_busqueda=codigo_abonado', $this->authHeaders());
        $respBuscar->assertStatus(200);
        $this->assertNotEmpty($respBuscar->json('data'));

        // Consulta de estado de cuenta
        $respCuenta = $this->getJson("/api/comercial/caja/estado-cuenta/{$abonado1->codigo}", $this->authHeaders());
        $respCuenta->assertStatus(200);
        $this->assertEquals(23.00, (float) $respCuenta->json('data.deuda_total'));

        // -------------------------------------------------------------
        // FASE 4: COBRO EN VENTANILLA DE FACTURA EN EFECTIVO (BS. 23.00)
        // -------------------------------------------------------------
        $respCobro1 = $this->postJson('/api/comercial/caja/cobrar', [
            'id_abonado' => $abonado1->id,
            'lecturas_ids' => [$lectura1->id],
            'cuotas_ids' => [],
            'codigo_metodo_pago' => 1, // Efectivo
            'nombre_razon_social' => $abonado1->nombre_completo,
            'numero_documento' => $abonado1->numero_documento,
            'codigo_tipo_documento_identidad' => 1,
        ], $this->authHeaders());

        $this->assertEquals(200, $respCobro1->status(), 'Error en cobro: ' . json_encode($respCobro1->json()));
        $respCobro1->assertJson(['success' => true]);
        $factura1Id = $respCobro1->json('data.factura.id');
        $this->assertNotNull($factura1Id);

        // Verificar que la lectura quedó PAGADA y enlazada a la sesión
        $lectura1->refresh();
        $this->assertEquals('PAGADO', $lectura1->estado_pago);
        $this->assertEquals($sesionId, $lectura1->id_sesion_caja);

        // -------------------------------------------------------------
        // FASE 5: COBRO EN VENTANILLA DE FACTURA MEDIANTE QR (BS. 50.00)
        // -------------------------------------------------------------
        $abonado2 = Abonado::create([
            'codigo' => '07002',
            'nombre_completo' => 'BEATRIZ FLORES CONDORI',
            'numero_documento' => '6712390',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->categoriaDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $lectura2 = LecturaMensual::create([
            'id_periodo' => $periodo->id,
            'id_abonado' => $abonado2->id,
            'lectura_anterior' => 200,
            'lectura_actual' => 220,
            'consumo_m3' => 20,
            'monto_agua' => 45.00,
            'monto_alcantarillado' => 5.00,
            'monto_descuento_ley1886' => 0.00,
            'total_facturado' => 50.00,
            'estado_pago' => 'PENDIENTE',
            '_estado' => 'ACTIVO',
        ]);

        $respCobro2 = $this->postJson('/api/comercial/caja/cobrar', [
            'id_abonado' => $abonado2->id,
            'lecturas_ids' => [$lectura2->id],
            'cuotas_ids' => [],
            'codigo_metodo_pago' => 7, // Pago con Transferencia / QR
            'nombre_razon_social' => $abonado2->nombre_completo,
            'numero_documento' => $abonado2->numero_documento,
            'codigo_tipo_documento_identidad' => 1,
        ], $this->authHeaders());

        $respCobro2->assertStatus(200)->assertJson(['success' => true]);

        // -------------------------------------------------------------
        // FASE 6: COBRO DE CUOTA DE CONVENIO DE PAGO EN EFECTIVO (BS. 40.00)
        // -------------------------------------------------------------
        $convenio = ConvenioPago::create([
            'numero_convenio' => 'CONV-TEST-001',
            'id_abonado' => $abonado1->id,
            'monto_deuda_total' => 120.00,
            'pago_inicial' => 0.00,
            'saldo_financiado' => 120.00,
            'plazo_meses' => 3,
            'monto_cuota_mensual' => 40.00,
            'fecha_suscripcion' => '2026-05-01',
            'estado' => 'VIGENTE',
        ]);

        $cuota1 = ConvenioCuota::create([
            'id_convenio' => $convenio->id,
            'numero_cuota' => 1,
            'periodo' => '05/2026',
            'monto_cuota' => 40.00,
            'fecha_vencimiento' => '2026-06-15',
            'estado_pago' => 'PENDIENTE',
        ]);

        $respCobroCuota = $this->postJson('/api/comercial/caja/cobrar', [
            'id_abonado' => $abonado1->id,
            'lecturas_ids' => [],
            'cuotas_ids' => [$cuota1->id],
            'codigo_metodo_pago' => 1, // Efectivo
            'nombre_razon_social' => $abonado1->nombre_completo,
            'numero_documento' => $abonado1->numero_documento,
            'codigo_tipo_documento_identidad' => 1,
        ], $this->authHeaders());

        $respCobroCuota->assertStatus(200)->assertJson(['success' => true]);
        $cuota1->refresh();
        $this->assertEquals('PAGADO', $cuota1->estado_pago);
        $this->assertEquals($sesionId, $cuota1->id_sesion_caja);

        // -------------------------------------------------------------
        // FASE 7: MOVIMIENTO DE CAJA CHICA (EGRESO DE BS. 13.00)
        // -------------------------------------------------------------
        $respMov = $this->postJson('/api/comercial/caja-sesiones/movimiento', [
            'id_sesion' => $sesionId,
            'tipo' => 'EGRESO',
            'monto' => 13.00,
            'concepto' => 'Compra de cinta de embalaje y recibos',
            'beneficiario' => 'Librería El Sol',
        ], $this->authHeaders());

        $respMov->assertStatus(201)->assertJson(['success' => true]);

        // -------------------------------------------------------------
        // FASE 8: VERIFICACIÓN DEL BALANCE EN TIEMPO REAL (RESUMEN ARQUEO)
        // -------------------------------------------------------------
        // Cálculos esperados:
        // Fondo inicial: Bs. 150.00
        // + Ventas Efectivo (Lectura 1): Bs. 23.00
        // + Cuota Convenio Efectivo: Bs. 40.00
        // - Egreso Caja Chica: Bs. 13.00
        // = Efectivo Físico Esperado en Gaveta: 150 + 23 + 40 - 13 = Bs. 200.00
        // Ventas QR (Banco, no suma a gaveta): Bs. 50.00
        $respResumen = $this->getJson("/api/comercial/caja-sesiones/resumen-arqueo?id_sesion={$sesionId}", $this->authHeaders());
        $respResumen->assertStatus(200)->assertJson(['success' => true]);

        $this->assertEquals(200.00, (float) $respResumen->json('data.totales.monto_esperado_efectivo'));
        $this->assertEquals(50.00, (float) $respResumen->json('data.totales.total_qr_banco'));
        $this->assertEquals(63.00, (float) $respResumen->json('data.totales.total_efectivo'));

        // -------------------------------------------------------------
        // FASE 9: ARQUEO FÍSICO Y CIERRE DE TURNO (CUADRADO EXACTO)
        // -------------------------------------------------------------
        // Conteo: 1 billete de Bs 200 = Bs. 200.00 exactos
        $desgloseBilletes = [
            'b200' => 1,
            'b100' => 0,
            'b50' => 0,
            'b20' => 0,
            'b10' => 0,
            'm5' => 0,
            'm2' => 0,
            'm1' => 0,
            'm050' => 0,
            'm020' => 0,
            'm010' => 0,
        ];

        $respCierre = $this->postJson('/api/comercial/caja-sesiones/cerrar', [
            'id_sesion' => $sesionId,
            'monto_cierre_declarado' => 200.00,
            'desglose_billetes' => $desgloseBilletes,
            'observaciones_cierre' => 'Cierre de ventanilla con cuadratura exacta 0.00',
        ], $this->authHeaders());

        $respCierre->assertStatus(200)->assertJson([
            'success' => true,
            'diferencia' => 0.00,
        ]);

        $sesion->refresh();
        $this->assertEquals('CERRADA', $sesion->estado);
        $this->assertEquals(200.00, (float) $sesion->monto_cierre_declarado);
        $this->assertEquals(0.00, (float) $sesion->diferencia);
        $this->assertNotNull($sesion->fecha_cierre);

        // -------------------------------------------------------------
        // FASE 10: GENERACIÓN Y DESCARGA DE LA PLANILLA OFICIAL EN PDF
        // -------------------------------------------------------------
        $respPdf = $this->get("/api/comercial/caja-sesiones/{$sesionId}/reporte-pdf", $this->authHeaders());
        $respPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $respPdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $respPdf->getContent());
    }
}
