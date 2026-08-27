<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rrhh\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RrhhPopulationAndReportingTest extends TestCase
{
    protected string $token;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Verificar que el padrón de personal y la estructura orgánica estén poblados.
     */
    public function test_padron_personal_and_organizational_hierarchy(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/rrhh/reportes/padron-personal');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertGreaterThanOrEqual(15, count($response->json('data')));

        // Verificar existencia de funcionarios clave
        $gerente = collect($response->json('data'))->firstWhere('ci', '4892104');
        $this->assertNotNull($gerente);
        $this->assertEquals('Carlos Franklin Mamani Quispe', $gerente['nombre_completo']);
        $this->assertEquals('Gerente General Ejecutivo', $gerente['cargo']);
        $this->assertEquals(16, $gerente['anios_cas']);
    }

    /**
     * Verificar cálculo de planilla mensual y bono de antigüedad según DS 21060.
     */
    public function test_calculo_planilla_sueldos_mensual(): void
    {
        $mes = (int)date('m');
        $anio = (int)date('Y');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/rrhh/reportes/planilla-sueldos?mes={$mes}&anio={$anio}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'mes' => $mes,
                'anio' => $anio,
            ]);

        $items = $response->json('data');
        $this->assertNotEmpty($items);

        // Verificar cálculo para funcionario con 16 años de antigüedad (34% s/ 3 SMN = 2550 Bs)
        $gerente = collect($items)->firstWhere('ci', '4892104');
        $this->assertNotNull($gerente);
        $this->assertEquals(18500.00, $gerente['haber_basico']);
        $this->assertEquals(34.0, $gerente['porcentaje_bono']);
        $this->assertEquals(2550.00, $gerente['bono_antiguedad']);
        $this->assertEquals(21050.00, $gerente['total_ganado']);
        $this->assertEquals(round(21050.00 * 0.1271, 2), $gerente['gestora_12_71']);
    }

    /**
     * Verificar generación y congelamiento de snapshot inmutable de planilla.
     */
    public function test_cierre_y_declaracion_inmutable_de_planilla(): void
    {
        // Usar mes histórico (e.g. mes 1) para no interferir con el mes en curso
        $mes = 1;
        $anio = 2026;

        // Limpiar previo si existía
        DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', 'SUELDOS_Y_SALARIOS')
            ->delete();

        // 1. Cerrar y Declarar Planilla
        $responseCierre = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/rrhh/reportes/cerrar-declarar-planilla', [
                'mes' => $mes,
                'anio' => $anio,
            ]);

        $responseCierre->assertStatus(201)
            ->assertJson([
                'success' => true,
                'cite_oficial' => 'PLA-EMAPA-01-2026',
            ]);

        // 2. Consultar nuevamente y verificar que devuelve el snapshot inmutable congelado
        $responseConsulta = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/rrhh/reportes/planilla-sueldos?mes={$mes}&anio={$anio}");

        $responseConsulta->assertStatus(200)
            ->assertJson([
                'success' => true,
                'es_declarada' => true,
                'estado_planilla' => 'DECLARADA',
                'cite_oficial' => 'PLA-EMAPA-01-2026',
            ]);
    }

    /**
     * Verificar emisión de Boleta Oficial de Pago Individual.
     */
    public function test_boleta_pago_individual_html(): void
    {
        $persona = Persona::where('nro_documento', '4892104')->first();
        $this->assertNotNull($persona);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/rrhh/reportes/boleta-pago/{$persona->id}/html");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'funcionario' => [
                        'ci' => '4892104',
                        'nombre_completo' => 'Carlos Franklin Mamani Quispe',
                    ],
                ],
            ]);

        $this->assertGreaterThan(0, $response->json('data.liquido_pagable'));
    }

    /**
     * Verificar emisión de Certificado Laboral Oficial con CITE.
     */
    public function test_certificado_trabajo_oficial_html(): void
    {
        $persona = Persona::where('nro_documento', '3928105')->first();
        $this->assertNotNull($persona);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/rrhh/reportes/certificado-trabajo/{$persona->id}/html");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'ci' => '3928105',
                    'funcionario' => 'Patricia Elena Vargas Morales',
                ],
            ]);

        $this->assertStringContainsString('CERT-RRHH-', $response->json('data.cite'));
    }

    /**
     * Verificar generador dinámico de reportes personalizados.
     */
    public function test_generador_reportes_personalizados(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/rrhh/reportes/generar-personalizado', [
                'columnas' => ['nombres', 'ci', 'cargo', 'unidad', 'tipo_contrato', 'anios_cas'],
                'tipo_contrato' => 'PLANTA',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $fila) {
            $this->assertEquals('PLANTA', $fila['tipo_contrato']);
            $this->assertArrayHasKey('nombres', $fila);
            $this->assertArrayHasKey('ci', $fila);
            $this->assertArrayHasKey('cargo', $fila);
            $this->assertArrayHasKey('unidad', $fila);
            $this->assertArrayHasKey('anios_cas', $fila);
        }
    }
}
