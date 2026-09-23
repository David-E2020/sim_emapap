<?php

declare(strict_types=1);

namespace Tests\Feature\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\ConvenioCuota;
use App\Models\Comercial\ConvenioPago;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\Medidor;
use App\Models\Comercial\OrdenTrabajo;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\Zona;
use App\Models\User;
use App\Services\Comercial\CobranzaAguaService;
use App\Services\Comercial\ConvenioService;
use App\Services\Comercial\CorteReconexionService;
use App\Services\Comercial\LecturacionService;
use App\Services\Comercial\TarifarioAguaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ComercialModuleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected string $token;
    protected Zona $zona;
    protected CategoriaTarifaria $catDom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);

        $this->zona = Zona::firstOrCreate(
            ['codigo' => 'TEST_CENTRAL'],
            ['nombre' => 'Zona Central de Prueba']
        );

        $this->catDom = CategoriaTarifaria::firstOrCreate(
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
     * 1. Test de precisión matemática del cálculo tarifario (FoxPro -> PHP)
     */
    public function test_calculo_tarifario_domiciliario_base_y_excedente(): void
    {
        /** @var TarifarioAguaService $service */
        $service = app(TarifarioAguaService::class);

        $abonado = Abonado::create([
            'codigo' => 'TEST01',
            'nombre_completo' => 'JUAN PEREZ TEST',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'tiene_alcantarillado' => true,
            'es_tercera_edad' => false,
            'estado_servicio' => 'ACTIVO',
        ]);

        // Caso A: Consumo de 4 m³ (dentro de los 6 m³ base)
        $resA = $service->calcularLiquidacion($abonado, 100.00, 104.00);
        $this->assertEquals(4.00, $resA['consumo_m3']);
        $this->assertEquals(12.60, $resA['monto_agua']);
        $this->assertEquals(2.00, $resA['monto_alcantarillado']);
        $this->assertEquals(0.00, $resA['monto_descuento_ley1886']);
        $this->assertEquals(14.60, $resA['total_facturado']);

        // Caso B: Consumo de 11 m³ (6 m³ base + 5 m³ excedente a Bs 2.10 = 10.50 Bs)
        $resB = $service->calcularLiquidacion($abonado, 100.00, 111.00);
        $this->assertEquals(11.00, $resB['consumo_m3']);
        $this->assertEquals(23.10, $resB['monto_agua']); // 12.60 + 10.50
        $this->assertEquals(2.00, $resB['monto_alcantarillado']);
        $this->assertEquals(25.10, $resB['total_facturado']);
    }

    /**
     * 2. Test del beneficio Ley 1886 (20% de descuento para Tercera Edad)
     */
    public function test_descuento_tercera_edad_ley_1886(): void
    {
        /** @var TarifarioAguaService $service */
        $service = app(TarifarioAguaService::class);

        $abonadoTerceraEdad = Abonado::create([
            'codigo' => 'TEST02',
            'nombre_completo' => 'ADULTO MAYOR BENEFICIARIO',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'tiene_alcantarillado' => true,
            'es_tercera_edad' => true, // Aplica 20%
            'estado_servicio' => 'ACTIVO',
        ]);

        // Consumo de 6 m³ -> Subtotal Bs 14.60. 20% de 14.60 es Bs 2.92. Total: 11.68 Bs
        $res = $service->calcularLiquidacion($abonadoTerceraEdad, 0.00, 6.00);
        $this->assertEquals(6.00, $res['consumo_m3']);
        $this->assertEquals(12.60, $res['monto_agua']);
        $this->assertEquals(2.00, $res['monto_alcantarillado']);
        $this->assertEquals(2.92, $res['monto_descuento_ley1886']);
        $this->assertEquals(11.68, $res['total_facturado']);
    }

    /**
     * 3. Test del ciclo de apertura de periodo y captura de lecturas
     */
    public function test_apertura_periodo_y_registro_lecturas(): void
    {
        /** @var LecturacionService $lecturaService */
        $lecturaService = app(LecturacionService::class);

        $medidor = Medidor::create([
            'numero_serie' => 'MED-TEST-001',
            'lectura_inicial' => 50.00,
        ]);

        $abonado = Abonado::create([
            'codigo' => 'TEST03',
            'nombre_completo' => 'USUARIO CON MEDIDOR',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'id_medidor_actual' => $medidor->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        // Apertura del periodo
        $periodo = $lecturaService->abrirPeriodo(
            9,
            2026,
            '2026-09-01',
            '2026-09-30',
            '2026-10-15'
        );

        $this->assertNotNull($periodo);
        $this->assertEquals('09/2026', $periodo->periodo);

        // Verificar que se inicializó la orden de lectura para el abonado con lectura_anterior = 50.00
        $lectura = LecturaMensual::where('id_periodo', $periodo->id)
            ->where('id_abonado', $abonado->id)
            ->first();

        $this->assertNotNull($lectura);
        $this->assertEquals(50.00, (float) $lectura->lectura_anterior);

        // Registrar lectura actual de 65.00 m³ (Consumo = 15 m³)
        $lecturaRegistrada = $lecturaService->registrarLectura(
            $lectura->id,
            65.00,
            false,
            'Lectura normal'
        );

        $this->assertEquals(15.00, (float) $lecturaRegistrada->consumo_m3);
        $this->assertEquals(31.50, (float) $lecturaRegistrada->monto_agua); // 12.60 + 9*2.10(18.90) = 31.50
        $this->assertEquals(33.50, (float) $lecturaRegistrada->total_facturado);

        // Liquidar el periodo
        $periodoLiquidado = $lecturaService->liquidarPeriodo($periodo->id);
        $this->assertEquals('FACTURADO', $periodoLiquidado->estado);

        // Verificar que el abonado ahora tiene mora de 1 mes y deuda de Bs 33.50
        $abonado->refresh();
        $this->assertEquals(1, $abonado->meses_mora);
        $this->assertEquals(33.50, (float) $abonado->saldo_deuda);
    }

    /**
     * 4. Test de cobro en ventanilla y enlace atómico con Facturación SIAT
     */
    public function test_cobro_en_ventanilla_y_emision_factura_siat(): void
    {
        /** @var CobranzaAguaService $cobranzaService */
        $cobranzaService = app(CobranzaAguaService::class);
        /** @var LecturacionService $lecturaService */
        $lecturaService = app(LecturacionService::class);

        $abonado = Abonado::create([
            'codigo' => 'TEST04',
            'nombre_completo' => 'CARLOS CLIENTE COBRO',
            'numero_documento' => '1234567',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $periodo = $lecturaService->abrirPeriodo(
            10,
            2026,
            '2026-10-01',
            '2026-10-31',
            '2026-11-15'
        );

        $lectura = LecturaMensual::where('id_periodo', $periodo->id)
            ->where('id_abonado', $abonado->id)
            ->first();

        $lecturaService->registrarLectura($lectura->id, 10.00);
        $lecturaService->liquidarPeriodo($periodo->id);

        $abonado->refresh();
        $this->assertGreaterThan(0, (float) $abonado->saldo_deuda);

        // Ejecutar Cobro en Ventanilla
        $resultado = $cobranzaService->cobrarEnVentanilla(
            $abonado->id,
            [$lectura->id],
            [],
            1, // Efectivo
            $abonado->nombre_completo,
            $abonado->numero_documento ?? '1234567',
            1, // CI
            null,
            'cliente@emapa.gob.bo',
            $this->user->id
        );

        $this->assertTrue($resultado['success']);
        $this->assertNotNull($resultado['factura']['id']);
        $this->assertNotEmpty($resultado['factura']['cuf']);
        $this->assertEquals(0.00, $resultado['saldo_restante']);
        $this->assertEquals(0, $resultado['meses_mora_restantes']);

        // Verificar que la lectura quedó en estado PAGADO con id_factura vinculado
        $lectura->refresh();
        $this->assertEquals('PAGADO', $lectura->estado_pago);
        $this->assertEquals($resultado['factura']['id'], $lectura->id_factura);
        $this->assertNotNull($lectura->fecha_pago);

        // Verificar que el abonado quedó sin saldo
        $abonado->refresh();
        $this->assertEquals(0.00, (float) $abonado->saldo_deuda);
        $this->assertEquals(0, $abonado->meses_mora);
    }

    /**
     * 5. Test de Convenio de Pagos (simulación y suscripción)
     */
    public function test_suscripcion_convenio_de_pagos(): void
    {
        /** @var ConvenioService $convenioService */
        $convenioService = app(ConvenioService::class);

        $abonado = Abonado::create([
            'codigo' => 'TEST05',
            'nombre_completo' => 'DEUDOR PARA CONVENIO',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'saldo_deuda' => 600.00,
            'meses_mora' => 6,
            'estado_servicio' => 'CORTADO',
        ]);

        // Simulación: Bs 600 deuda, Bs 100 pago inicial, a 5 cuotas
        $sim = $convenioService->simularConvenio(600.00, 100.00, 5);
        $this->assertEquals(500.00, $sim['saldo_financiado']);
        $this->assertEquals(100.00, $sim['monto_cuota_promedio']);
        $this->assertCount(5, $sim['cuotas']);

        // Suscripción formal
        $convenio = $convenioService->suscribirConvenio(
            $abonado->id,
            100.00,
            5,
            'Convenio por regularización de deuda acumulada',
            $this->user->id
        );

        $this->assertNotNull($convenio);
        $this->assertEquals('VIGENTE', $convenio->estado);
        $this->assertCount(5, $convenio->cuotas);

        // Al suscribir convenio, el abonado que estaba CORTADO se rehabilita temporalmente a ACTIVO
        $abonado->refresh();
        $this->assertEquals('ACTIVO', $abonado->estado_servicio);
    }

    /**
     * 6. Test de Control Operativo de Cortes y Reconexiones
     */
    public function test_cortes_por_mora_y_reconexiones(): void
    {
        /** @var CorteReconexionService $corteService */
        $corteService = app(CorteReconexionService::class);

        $abonadoMoroso = Abonado::create([
            'codigo' => 'TEST06',
            'nombre_completo' => 'USUARIO CON MORA ALTA',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'saldo_deuda' => 150.00,
            'meses_mora' => 3, // Mora >= 2 meses
            'estado_servicio' => 'EN_MORA',
        ]);

        // 1. Debe aparecer en candidatos a corte
        $candidatos = $corteService->obtenerAbonadosParaCorte(2);
        $this->assertTrue($candidatos->contains('id', $abonadoMoroso->id));

        // 2. Generar orden de corte
        $resCorte = $corteService->generarOrdenesCorte(
            [$abonadoMoroso->id],
            Carbon::tomorrow()->toDateString()
        );

        $this->assertEquals(1, $resCorte['total_generadas']);
        $orden = $resCorte['ordenes'][0];

        // 3. Ejecutar corte en campo
        $ordenEjecutada = $corteService->ejecutarCorte(
            $orden->id,
            154.20,
            'PREC-99881',
            'Corte de llave de paso con precinto'
        );

        $this->assertEquals('EJECUTADO', $ordenEjecutada->estado);

        // El abonado pasa a CORTADO
        $abonadoMoroso->refresh();
        $this->assertEquals('CORTADO', $abonadoMoroso->estado_servicio);
        $this->assertNotNull($abonadoMoroso->fecha_ultimo_corte);
    }

    /**
     * 7. Test de generación del Aviso de Cobranza (Prefactura) en PDF
     */
    public function test_generacion_aviso_cobranza_pdf(): void
    {
        /** @var \App\Services\Comercial\DocumentoComercialPdfService $pdfService */
        $pdfService = app(\App\Services\Comercial\DocumentoComercialPdfService::class);
        /** @var LecturacionService $lecturaService */
        $lecturaService = app(LecturacionService::class);

        $abonado = Abonado::create([
            'codigo' => 'TEST07',
            'nombre_completo' => 'MARIA AVISO COBRANZA',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $periodo = $lecturaService->abrirPeriodo(
            11,
            2026,
            '2026-11-01',
            '2026-11-30',
            '2026-12-15'
        );

        $lectura = LecturaMensual::where('id_periodo', $periodo->id)
            ->where('id_abonado', $abonado->id)
            ->first();

        $lecturaService->registrarLectura($lectura->id, 20.00);

        $pdfBinario = $pdfService->generarAvisoCobranzaPdf($lectura);
        $this->assertNotEmpty($pdfBinario);
        $this->assertStringStartsWith('%PDF', $pdfBinario);
    }

    /**
     * 8. Test de Cambio y Reemplazo de Medidor
     */
    public function test_cambio_y_reemplazo_de_medidor(): void
    {
        $medidorViejo = Medidor::create([
            'numero_serie' => 'VIEJO-999',
            'lectura_inicial' => 100.00,
            'estado' => 'OPERATIVO',
        ]);

        $abonado = Abonado::create([
            'codigo' => 'TEST08',
            'nombre_completo' => 'CAMBIO DE MEDIDOR USUARIO',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'id_medidor_actual' => $medidorViejo->id,
            'tiene_medidor' => true,
            'estado_servicio' => 'ACTIVO',
        ]);

        $response = $this->postJson("/api/comercial/abonados/{$abonado->id}/cambiar-medidor", [
            'numero_serie_nuevo' => 'NUEVO-1000',
            'marca' => 'Sensus',
            'diametro' => '1/2"',
            'lectura_final_anterior' => 145.50,
            'lectura_inicial_nuevo' => 0.00,
            'motivo' => 'Medidor anterior trancado y con vidrio roto',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $abonado->refresh();
        $this->assertEquals('NUEVO-1000', $abonado->medidorActual->numero_serie);

        $medidorViejo->refresh();
        $this->assertEquals('CAMBIADO', $medidorViejo->estado);

        // Verificar orden de trabajo de cambio
        $ot = OrdenTrabajo::where('id_abonado', $abonado->id)->where('tipo_orden', 'CAMBIO_MEDIDOR')->first();
        $this->assertNotNull($ot);
        $this->assertEquals(145.50, (float) $ot->lectura_en_corte);
    }

    /**
     * 9. Test de Baja Definitiva de Servicio
     */
    public function test_baja_definitiva_abonado(): void
    {
        $abonado = Abonado::create([
            'codigo' => 'TEST09',
            'nombre_completo' => 'ABONADO SOLICITUD BAJA',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $response = $this->postJson("/api/comercial/abonados/{$abonado->id}/dar-baja", [
            'motivo' => 'Inmueble en demolición definitiva y solicitud voluntaria',
            'lectura_retiro' => 340.00,
        ], $this->authHeaders());

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $abonado->refresh();
        $this->assertEquals('BAJA', $abonado->estado_servicio);

        $ot = OrdenTrabajo::where('id_abonado', $abonado->id)->where('tipo_orden', 'BAJA_DEFINITIVA')->first();
        $this->assertNotNull($ot);
        $this->assertEquals(340.00, (float) $ot->lectura_en_corte);
    }

    /**
     * 10. Test de Generación de Extracto Histórico en PDF
     */
    public function test_generacion_extracto_historico_pdf(): void
    {
        $abonado = Abonado::create([
            'codigo' => 'TEST10',
            'nombre_completo' => 'ABONADO CON HISTORIAL',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $response = $this->get("/api/comercial/abonados/{$abonado->id}/extracto/pdf", $this->authHeaders());
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * 11. Test de Emisión y Descarga de Recibo de Caja en PDF
     */
    public function test_emision_y_descarga_recibo_caja_pdf(): void
    {
        $abonado = Abonado::create([
            'codigo' => 'TEST11',
            'nombre_completo' => 'PAGADOR DE DERECHO CONEXION',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $postResponse = $this->postJson('/api/comercial/caja/recibos', [
            'id_abonado' => $abonado->id,
            'nombre_cliente' => 'PAGADOR DE DERECHO CONEXION',
            'documento_cliente' => '9876543',
            'concepto_tipo' => 'DERECHO_CONEXION',
            'descripcion' => 'Pago por nuevo derecho de conexión red matriz',
            'monto_total' => 650.00,
        ], $this->authHeaders());

        $postResponse->assertStatus(201);
        $reciboId = $postResponse->json('data.id');

        $pdfResponse = $this->get("/api/comercial/caja/recibos/{$reciboId}/pdf", $this->authHeaders());
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
    }

    /**
     * 12. Test de Descarga de Orden de Trabajo en PDF
     */
    public function test_descarga_orden_trabajo_pdf(): void
    {
        $abonado = Abonado::create([
            'codigo' => 'TEST12',
            'nombre_completo' => 'ABONADO EN CORTE',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'CORTADO',
        ]);

        $orden = OrdenTrabajo::create([
            'numero_orden' => 'ORD-TEST-001',
            'id_abonado' => $abonado->id,
            'tipo_orden' => 'CORTE',
            'motivo' => 'Mora de 3 meses en pago de agua',
            'fecha_programada' => date('Y-m-d'),
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->get("/api/comercial/cortes/ordenes/{$orden->id}/pdf", $this->authHeaders());
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * 13. Test de Validación Secuencial: Rechaza saltear meses anteriores impagos (FACTIFIV)
     */
    public function test_cobro_secuencial_cronologico_rechaza_salteo_de_meses(): void
    {
        /** @var CobranzaAguaService $cobranzaService */
        $cobranzaService = app(CobranzaAguaService::class);
        /** @var LecturacionService $lecturaService */
        $lecturaService = app(LecturacionService::class);

        $abonado = Abonado::create([
            'codigo' => 'TEST13',
            'nombre_completo' => 'ABONADO DEUDA SECUENCIAL',
            'numero_documento' => '99887766',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $p1 = PeriodoFacturacion::firstOrCreate(['periodo' => '01/2026'], [
            'mes' => 1, 'gestion' => 2026, 'fecha_inicio_consumo' => '2026-01-01', 'fecha_fin_consumo' => '2026-01-31', 'fecha_vencimiento_pago' => '2026-02-25', 'estado' => 'CERRADO',
        ]);
        $p2 = PeriodoFacturacion::firstOrCreate(['periodo' => '02/2026'], [
            'mes' => 2, 'gestion' => 2026, 'fecha_inicio_consumo' => '2026-02-01', 'fecha_fin_consumo' => '2026-02-28', 'fecha_vencimiento_pago' => '2026-03-25', 'estado' => 'CERRADO',
        ]);

        $l1 = LecturaMensual::firstOrCreate(['id_periodo' => $p1->id, 'id_abonado' => $abonado->id], [
            'consumo_m3' => 6.0, 'monto_agua' => 12.60, 'monto_alcantarillado' => 2.00, 'total_facturado' => 14.60, 'estado_pago' => 'PENDIENTE',
        ]);
        $l2 = LecturaMensual::firstOrCreate(['id_periodo' => $p2->id, 'id_abonado' => $abonado->id], [
            'consumo_m3' => 6.0, 'monto_agua' => 12.60, 'monto_alcantarillado' => 2.00, 'total_facturado' => 14.60, 'estado_pago' => 'PENDIENTE',
        ]);

        // Intento de cobrar el segundo mes ($l2) omitiendo el primer mes ($l1)
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Las facturas deben cancelarse en orden cronológico estricto');

        $cobranzaService->cobrarEnVentanilla(
            idAbonado: $abonado->id,
            lecturasIds: [$l2->id],
            cuotasIds: [],
            codigoMetodoPago: 1,
            razonSocial: 'TEST',
            numeroDocumento: '99887766'
        );
    }

    /**
     * 14. Test de Cobro Secuencial Exitoso: De la más antigua a la más moderna
     */
    public function test_cobro_secuencial_cronologico_exitoso(): void
    {
        /** @var CobranzaAguaService $cobranzaService */
        $cobranzaService = app(CobranzaAguaService::class);

        $abonado = Abonado::create([
            'codigo' => 'TEST14',
            'nombre_completo' => 'ABONADO SECUENCIAL EXITOSO',
            'numero_documento' => '88776655',
            'id_zona' => $this->zona->id,
            'id_categoria' => $this->catDom->id,
            'estado_servicio' => 'ACTIVO',
        ]);

        $p1 = PeriodoFacturacion::firstOrCreate(['periodo' => '03/2026'], [
            'mes' => 3, 'gestion' => 2026, 'fecha_inicio_consumo' => '2026-03-01', 'fecha_fin_consumo' => '2026-03-31', 'fecha_vencimiento_pago' => '2026-04-25', 'estado' => 'CERRADO',
        ]);
        $p2 = PeriodoFacturacion::firstOrCreate(['periodo' => '04/2026'], [
            'mes' => 4, 'gestion' => 2026, 'fecha_inicio_consumo' => '2026-04-01', 'fecha_fin_consumo' => '2026-04-30', 'fecha_vencimiento_pago' => '2026-05-25', 'estado' => 'CERRADO',
        ]);

        $l1 = LecturaMensual::firstOrCreate(['id_periodo' => $p1->id, 'id_abonado' => $abonado->id], [
            'consumo_m3' => 6.0, 'monto_agua' => 12.60, 'monto_alcantarillado' => 2.00, 'total_facturado' => 14.60, 'estado_pago' => 'PENDIENTE',
        ]);
        $l2 = LecturaMensual::firstOrCreate(['id_periodo' => $p2->id, 'id_abonado' => $abonado->id], [
            'consumo_m3' => 6.0, 'monto_agua' => 12.60, 'monto_alcantarillado' => 2.00, 'total_facturado' => 14.60, 'estado_pago' => 'PENDIENTE',
        ]);

        // Pagar primero 1 mes (el más antiguo $l1)
        $res1 = $cobranzaService->cobrarEnVentanilla(
            idAbonado: $abonado->id,
            lecturasIds: [$l1->id],
            cuotasIds: [],
            codigoMetodoPago: 1,
            razonSocial: 'TEST SECUENCIAL',
            numeroDocumento: '88776655'
        );

        $this->assertTrue($res1['success']);
        $l1->refresh();
        $this->assertEquals('PAGADO', $l1->estado_pago);

        // Ahora pagar el siguiente mes ($l2)
        $res2 = $cobranzaService->cobrarEnVentanilla(
            idAbonado: $abonado->id,
            lecturasIds: [$l2->id],
            cuotasIds: [],
            codigoMetodoPago: 1,
            razonSocial: 'TEST SECUENCIAL',
            numeroDocumento: '88776655'
        );

        $this->assertTrue($res2['success']);
        $l2->refresh();
        $this->assertEquals('PAGADO', $l2->estado_pago);
    }

    public function test_aportes_conexiones_listado_y_contrato_pdf(): void
    {
        $aporte = \App\Models\Comercial\AporteConexion::firstOrCreate(
            ['codigo_socio' => 'TEST01', 'tipo_servicio' => 'AGUA'],
            [
                'periodo' => '09/2026',
                'nombre_socio' => 'BENEFICIARIO DE PRUEBA',
                'zona' => 'CENTRO',
                'estado' => 'ACTIVO',
                'fecha' => '2026-09-17',
                'aporte' => 294.90,
                'instalacion' => 1592.10,
                'total' => 1887.00,
                'plazo' => 1,
                'pagado' => true,
                'fecha_pago' => '2026-09-17',
                'factura' => '9999',
            ]
        );

        $responseIndex = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/comercial/aportes?search=TEST01');
        $responseIndex->assertStatus(200)
            ->assertJsonPath('success', true);

        $responsePdf = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get("/api/comercial/aportes/{$aporte->id}/contrato-pdf");
        $responsePdf->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }
}


