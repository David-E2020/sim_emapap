<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\Facturacion\Factura;
use App\Models\Facturacion\TransaccionQr;
use App\Models\User;
use App\Services\Facturacion\CobroQrSimpleService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

use Tymon\JWTAuth\Facades\JWTAuth;

class CobroQrSimpleAndCucuHybridTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_generar_cobro_qr_simple_emvco(): void
    {
        $service = app(CobroQrSimpleService::class);
        $tx = $service->generarTransaccionQr([
            'monto' => 150.50,
            'glosa' => 'Prueba Pago Agua Abonado 1024',
            'id_usuario' => $this->user->id,
            'minutos_vigencia' => 10,
        ]);

        $this->assertInstanceOf(TransaccionQr::class, $tx);
        $this->assertEquals(TransaccionQr::ESTADO_PENDING, $tx->estado);
        $this->assertEquals(150.50, (float) $tx->monto);
        $this->assertStringStartsWith('QR_', $tx->uuid);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $tx->qr_imagen_base64);

        // Validar formato EMVCo boliviano
        $this->assertStringContainsString('BO.GOB.ASOBAN.SIMPLE', $tx->qr_payload);
        $this->assertStringContainsString('EMAPAP PATACAMAYA', $tx->qr_payload);
        $this->assertStringContainsString('150.50', $tx->qr_payload);
    }

    public function test_api_generar_consultar_y_confirmar_pago_qr(): void
    {
        $headers = $this->authHeaders();

        // 1. Generar QR
        $resGen = $this->postJson('/api/facturacion/cobros-qr/generar', [
            'monto' => 85.00,
            'glosa' => 'Cobro servicio agua',
            'minutos_vigencia' => 10,
        ], $headers);

        $resGen->assertStatus(201)
            ->assertJsonPath('success', true);

        $uuid = $resGen->json('data.uuid');
        $this->assertNotEmpty($uuid);

        // 2. Consultar estado (Polling)
        $resEst = $this->getJson("/api/facturacion/cobros-qr/{$uuid}/estado", $headers);
        $resEst->assertStatus(200)
            ->assertJsonPath('estado', 'PENDING');
        $this->assertEquals(85.0, (float) $resEst->json('monto'));

        // 3. Confirmar pago
        $resConf = $this->postJson("/api/facturacion/cobros-qr/{$uuid}/confirmar", [
            'transaccion_banco_id' => 'BCB-TEST-998822',
            'banco_origen' => 'BANCO UNION MOVIL',
        ], $headers);

        $resConf->assertStatus(200)
            ->assertJsonPath('success', true);

        // 4. Verificar que el estado ahora sea COMPLETED
        $resEst2 = $this->getJson("/api/facturacion/cobros-qr/{$uuid}/estado", $headers);
        $resEst2->assertStatus(200)
            ->assertJsonPath('estado', 'COMPLETED')
            ->assertJsonPath('transaccion_banco_id', 'BCB-TEST-998822');
    }

    public function test_anulacion_administrativa_rnd_102600000025_y_reversion(): void
    {
        $headers = $this->authHeaders();

        // Tomar una factura existente o crear una
        $factura = Factura::where('estado_factura', 'VALIDADA')->first();
        if (!$factura) {
            $this->markTestSkipped('No hay facturas válidas para probar anulación.');
        }

        // 1. Anulación Administrativa
        $resAnulacion = $this->postJson("/api/facturacion/facturas/{$factura->id}/anular-administrativa", [
            'codigo_motivo' => 5, // AUTORIZACIÓN ADMINISTRATIVA
            'nro_resolucion' => 'RA-GDLPZ-2026-TEST-001',
            'fecha_resolucion' => '2026-09-23',
            'justificacion' => 'Anulación fuera de plazo reglamentario',
        ], $headers);

        $resAnulacion->assertStatus(200)
            ->assertJsonPath('success', true);

        $factura->refresh();
        $this->assertEquals(Factura::ESTADO_CANCELLED, $factura->estado_factura);
        $this->assertTrue((bool) $factura->es_anulacion_administrativa);
        $this->assertEquals('RA-GDLPZ-2026-TEST-001', $factura->nro_resolucion_administrativa);

        // 2. Revertir Anulación
        $resRev = $this->postJson("/api/facturacion/facturas/{$factura->id}/revertir-anulacion", [], $headers);
        $resRev->assertStatus(200)
            ->assertJsonPath('success', true);

        $factura->refresh();
        $this->assertEquals(Factura::ESTADO_VALIDATED, $factura->estado_factura);
        $this->assertNotNull($factura->reversion_anulacion_fecha);
    }
}
