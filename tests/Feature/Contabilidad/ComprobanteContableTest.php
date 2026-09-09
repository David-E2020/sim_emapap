<?php

declare(strict_types=1);

namespace Tests\Feature\Contabilidad;

use App\Models\Contabilidad\CentroCosto;
use App\Models\Contabilidad\Comprobante;
use App\Models\Contabilidad\PlanCuenta;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ComprobanteContableTest extends TestCase
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
     * Verificar consulta de catálogo contable y cuentas imputables (Nivel 5).
     */
    public function test_puede_consultar_plan_de_cuentas_y_cuentas_imputables(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/plan-cuentas');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);

        $responseImputables = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/contabilidad/plan-cuentas/imputables');

        $responseImputables->assertStatus(200)
            ->assertJsonStructure(['data']);

        $imputables = $responseImputables->json('data');
        $this->assertNotEmpty($imputables);
        foreach ($imputables as $cta) {
            $this->assertTrue((bool)$cta['es_imputable']);
        }
    }

    /**
     * Verificar creación de nueva cuenta contable.
     */
    public function test_puede_registrar_nueva_cuenta_contable(): void
    {
        $payload = [
            'codigo' => '1.1.1.01.999',
            'nombre' => 'Caja Chica Emergencias Test',
            'nivel' => 5,
            'naturaleza' => 'DEUDOR',
            'tipo_cuenta' => 'ACTIVO',
            'es_imputable' => true,
            'estado' => true,
            'descripcion' => 'Cuenta de prueba automatizada',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/plan-cuentas', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.codigo', '1.1.1.01.999');

        $this->assertDatabaseHas('contabilidad.plan_cuentas', [
            'codigo' => '1.1.1.01.999',
            'nombre' => 'Caja Chica Emergencias Test',
        ]);
    }

    /**
     * Verificar registro exitoso de comprobante manual con estricta Partida Doble (Debe = Haber).
     */
    public function test_puede_registrar_comprobante_manual_balanceado(): void
    {
        $ctaCaja = PlanCuenta::where('codigo', '1.1.1.01.001')->firstOrFail();
        $ctaBanco = PlanCuenta::where('codigo', '1.1.1.02.001')->firstOrFail();
        $centro = CentroCosto::first();

        $payload = [
            'tipo' => 'CD',
            'fecha' => '2026-03-15',
            'beneficiario' => 'Banco Unión S.A.',
            'glosa' => 'Traspaso de fondos de Caja Chica a Banco Cuenta Fiscal',
            'detalles' => [
                [
                    'plan_cuenta_id' => $ctaBanco->id,
                    'centro_costo_id' => $centro ? $centro->id : null,
                    'glosa' => 'Depósito en cuenta fiscal',
                    'debe' => 1500.50,
                    'haber' => 0.00,
                ],
                [
                    'plan_cuenta_id' => $ctaCaja->id,
                    'centro_costo_id' => $centro ? $centro->id : null,
                    'glosa' => 'Salida de efectivo de ventanilla',
                    'debe' => 0.00,
                    'haber' => 1500.50,
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/comprobantes', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_debe', 1500.5)
            ->assertJsonPath('data.total_haber', 1500.5)
            ->assertJsonPath('data.estado', 'APROBADO');

        $comprobanteId = $response->json('data.id');
        $this->assertNotNull($comprobanteId);

        $this->assertDatabaseHas('contabilidad.comprobantes', [
            'id' => $comprobanteId,
            'tipo' => 'DIARIO',
            'total_debe' => 1500.50,
            'total_haber' => 1500.50,
        ]);
    }

    /**
     * Verificar rechazo categórico de comprobante desbalanceado (Debe != Haber).
     */
    public function test_rechaza_comprobante_desbalanceado_por_violacion_partida_doble(): void
    {
        $ctaCaja = PlanCuenta::where('codigo', '1.1.1.01.001')->firstOrFail();
        $ctaBanco = PlanCuenta::where('codigo', '1.1.1.02.001')->firstOrFail();

        $payload = [
            'tipo' => 'CD',
            'fecha' => '2026-03-15',
            'beneficiario' => 'Operación Desbalanceada Test',
            'glosa' => 'Intento de registro con Debe y Haber desiguales',
            'detalles' => [
                [
                    'plan_cuenta_id' => $ctaBanco->id,
                    'glosa' => 'Línea Debe',
                    'debe' => 1000.00,
                    'haber' => 0.00,
                ],
                [
                    'plan_cuenta_id' => $ctaCaja->id,
                    'glosa' => 'Línea Haber menor',
                    'debe' => 0.00,
                    'haber' => 800.00, // Descuadre de 200 Bs
                ],
            ],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/comprobantes', $payload);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Verificar generación y descarga de comprobante en PDF oficial con 3 niveles de firma SAFCO.
     */
    public function test_puede_descargar_comprobante_en_pdf_oficial(): void
    {
        $ctaCaja = PlanCuenta::where('codigo', '1.1.1.01.001')->firstOrFail();
        $ctaBanco = PlanCuenta::where('codigo', '1.1.1.02.001')->firstOrFail();

        // Crear comprobante vía endpoint oficial
        $payload = [
            'tipo' => 'CI',
            'fecha' => '2026-03-15',
            'beneficiario' => 'Abonado Prueba PDF',
            'glosa' => 'Cobranza en ventanilla para emisión de PDF oficial',
            'detalles' => [
                [
                    'plan_cuenta_id' => $ctaCaja->id,
                    'glosa' => 'Ingreso caja efectivo',
                    'debe' => 350.00,
                    'haber' => 0.00,
                ],
                [
                    'plan_cuenta_id' => $ctaBanco->id,
                    'glosa' => 'Ingreso contrapartida',
                    'debe' => 0.00,
                    'haber' => 350.00,
                ],
            ],
        ];

        $postRes = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/comprobantes', $payload);

        $postRes->assertStatus(201);
        $comprobanteId = $postRes->json('data.id');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get('/api/contabilidad/comprobantes/' . $comprobanteId . '/pdf');

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * Verificar anulación de comprobante contable.
     */
    public function test_puede_anular_comprobante_con_justificacion(): void
    {
        $ctaCaja = PlanCuenta::where('codigo', '1.1.1.01.001')->firstOrFail();
        $ctaBanco = PlanCuenta::where('codigo', '1.1.1.02.001')->firstOrFail();

        $payload = [
            'tipo' => 'CD',
            'fecha' => '2026-03-15',
            'beneficiario' => 'Operación a Anular',
            'glosa' => 'Asiento contable a ser anulado formalmente',
            'detalles' => [
                [
                    'plan_cuenta_id' => $ctaCaja->id,
                    'glosa' => 'Línea 1',
                    'debe' => 75.00,
                    'haber' => 0.00,
                ],
                [
                    'plan_cuenta_id' => $ctaBanco->id,
                    'glosa' => 'Línea 2',
                    'debe' => 0.00,
                    'haber' => 75.00,
                ],
            ],
        ];

        $postRes = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/contabilidad/comprobantes', $payload);

        $postRes->assertStatus(201);
        $comp = $postRes->json('data');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/contabilidad/comprobantes/' . $comp['id'] . '/anular', [
                'motivo' => 'Error de digitación en monto por el cajero',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', 'ANULADO');

        $this->assertDatabaseHas('contabilidad.comprobantes', [
            'id' => $comp['id'],
            'estado' => 'ANULADO',
        ]);
    }
}
