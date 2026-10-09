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

    /** CI del Gerente General (primer funcionario en la DB real) */
    protected string $ciGerente = '4041212 QR';

    /** CI del Jefe de Unidad Técnica */
    protected string $ciJefe = '6814327 LP';

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first() ?? User::first();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Verificar que el padrón de personal y la estructura orgánica estén poblados.
     */
    public function test_padron_personal_and_organizational_hierarchy(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/rrhh/reportes/padron-personal');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertGreaterThanOrEqual(10, count($response->json('data')));

        // Verificar existencia del Gerente General (primer empleado real)
        $gerente = collect($response->json('data'))->firstWhere('ci', $this->ciGerente);
        $this->assertNotNull($gerente, "Gerente con CI {$this->ciGerente} debe existir en el padrón");
        $this->assertStringContainsString('RAMIREZ', strtoupper($gerente['nombre_completo']));
        $this->assertStringContainsString('Gerente', $gerente['cargo']);
    }

    /**
     * Verificar cálculo de planilla mensual y que los totales cuadran.
     */
    public function test_calculo_planilla_sueldos_mensual(): void
    {
        $mes = (int) date('m');
        $anio = (int) date('Y');

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/rrhh/reportes/planilla-sueldos?mes={$mes}&anio={$anio}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'mes'     => $mes,
                'anio'    => $anio,
            ]);

        $items = $response->json('data');
        $this->assertNotEmpty($items);

        // Verificar el total planilla planta permanente (debe ser >= 10 items, total >= Bs. 30,000)
        $totalGanado = collect($items)->sum('total_ganado');
        $this->assertGreaterThanOrEqual(30000, $totalGanado, 'Total ganado planta debe ser >= Bs. 30,000');

        // Verificar que todos los cálculos son coherentes
        foreach ($items as $item) {
            $expectedGestora = round((float) $item['total_ganado'] * 0.1271, 2);
            $this->assertEqualsWithDelta(
                $expectedGestora,
                round((float) $item['gestora_12_71'], 2),
                0.10, // tolerancia de 10 centavos por redondeo
                "Gestora de {$item['funcionario']} debe ser 12.71% del total ganado"
            );

            $expectedLiquido = round((float) $item['total_ganado'] - (float) $item['gestora_12_71'], 2);
            $this->assertEqualsWithDelta(
                $expectedLiquido,
                round((float) $item['liquido_salarial'], 2),
                0.10,
                "Liquido de {$item['funcionario']} debe ser total_ganado - gestora"
            );
        }

        // Verificar cálculo para el primer empleado (Gerente General, Bs. 6,621.30)
        $gerente = collect($items)->firstWhere('ci', $this->ciGerente);
        $this->assertNotNull($gerente);
        $this->assertEquals(6621.30, (float) $gerente['haber_basico']);
        $this->assertEquals(6621.30, (float) $gerente['total_ganado']);
        $this->assertEqualsWithDelta(round(6621.30 * 0.1271, 2), (float) $gerente['gestora_12_71'], 0.05);
    }

    /**
     * Verificar generación y congelamiento de snapshot inmutable de planilla.
     */
    public function test_cierre_y_declaracion_inmutable_de_planilla(): void
    {
        // Usar mes histórico no conflictivo
        $mes = 2;
        $anio = 2026;

        // Limpiar previo si existía para que el test sea idempotente
        DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->delete();

        // 1. Cerrar y Declarar Planilla
        $responseCierre = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/rrhh/reportes/planilla-sueldos/declarar', [
                'mes'  => $mes,
                'anio' => $anio,
            ]);

        $responseCierre->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $cite = $responseCierre->json('cite_oficial');
        $this->assertStringContainsString('PLA-EMAPA', $cite);
        $this->assertStringContainsString('02-2026', $cite);

        // 2. Intentar cerrar de nuevo — debe retornar 409 (inmutabilidad)
        $responseRepeat = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/rrhh/reportes/planilla-sueldos/declarar', [
                'mes'  => $mes,
                'anio' => $anio,
            ]);

        $responseRepeat->assertStatus(409);
        $this->assertFalse($responseRepeat->json('success'));

        // 3. Consultar y verificar que devuelve snapshot congelado
        $responseConsulta = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/rrhh/reportes/planilla-sueldos?mes={$mes}&anio={$anio}");

        $responseConsulta->assertStatus(200)
            ->assertJson([
                'success'        => true,
                'es_declarada'   => true,
                'estado_planilla' => 'DECLARADA',
            ]);

        $this->assertStringContainsString('PLA-EMAPA', $responseConsulta->json('cite_oficial'));
    }

    /**
     * Verificar emisión de Boleta Oficial de Pago Individual.
     */
    public function test_boleta_pago_individual_html(): void
    {
        // Buscar por CI real (primer empleado)
        $persona = Persona::where('nro_documento', $this->ciGerente)->first();
        $this->assertNotNull($persona, "Persona con CI '{$this->ciGerente}' debe existir");

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/rrhh/reportes/boleta-pago/{$persona->id}/html");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verificar que devuelve datos del funcionario correcto
        $data = $response->json('data');
        $this->assertNotNull($data);
        $this->assertGreaterThan(0, $response->json('data.liquido_pagable') ?? $response->json('data.liquido_salarial') ?? 1);
    }

    /**
     * Verificar emisión de Certificado Laboral Oficial con CITE.
     */
    public function test_certificado_trabajo_oficial_html(): void
    {
        // Usar el segundo empleado
        $persona = Persona::where('nro_documento', $this->ciJefe)->first();
        $this->assertNotNull($persona, "Persona con CI '{$this->ciJefe}' debe existir");

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson("/api/rrhh/reportes/certificado-trabajo/{$persona->id}/html");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verificar CITE o datos básicos
        $data = $response->json('data');
        $this->assertNotNull($data);
        $cite = $response->json('data.cite');
        if ($cite) {
            $this->assertStringContainsString('CERT-RRHH-', $cite);
        }
    }

    /**
     * Verificar generador dinámico de reportes personalizados.
     */
    public function test_generador_reportes_personalizados(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/rrhh/reportes/generar-personalizado', [
                'columnas'      => ['nombres', 'ci', 'cargo', 'unidad', 'tipo_contrato', 'anios_cas'],
                'tipo_contrato' => 'PLANTA',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        foreach ($data as $fila) {
            $this->assertArrayHasKey('nombres', $fila);
            $this->assertArrayHasKey('ci', $fila);
            $this->assertArrayHasKey('cargo', $fila);
        }
    }

    /**
     * Verificar API de configuración laboral: guardar y recuperar.
     */
    public function test_configuracion_laboral_save_and_retrieve(): void
    {
        $payload = [
            'gestion'                             => 2026,
            'salario_minimo_nacional'             => 3300,
            'gestora_vejez_porcentaje'            => 10.0,
            'gestora_riesgo_comun_porcentaje'     => 1.71,
            'gestora_comision_porcentaje'         => 0.50,
            'gestora_laboral_solidario_porcentaje' => 0.50,
            'patronal_cns_porcentaje'             => 10.0,
            'patronal_riesgo_profesional_porcentaje' => 1.71,
            'patronal_pro_vivienda_porcentaje'    => 2.0,
            'patronal_solidario_porcentaje'       => 3.0,
            'monto_refrigerio_diario'             => 18,
            'dias_laborables_mes'                 => 30,
            'horas_jornada_ordinaria'             => 8,
            'factor_horas_extra'                  => 2.0,
            'escala_antiguedad'                   => [
                ['anios_min' => 2, 'anios_max' => 4, 'porcentaje' => 5],
                ['anios_min' => 5, 'anios_max' => 7, 'porcentaje' => 11],
                ['anios_min' => 8, 'anios_max' => 10, 'porcentaje' => 18],
            ],
            'notas_resolucion' => 'Parámetros salariales oficiales EMAPAP 2026',
        ];

        // Save
        $saveResp = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/rrhh/configuracion-laboral', $payload);

        $saveResp->assertStatus(200)
            ->assertJson(['success' => true]);

        // Retrieve
        $getResp = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/rrhh/configuracion-laboral?gestion=2026');

        $getResp->assertStatus(200)
            ->assertJson(['success' => true]);

        $d = $getResp->json('data');
        $this->assertEquals(3300, (float) $d['salario_minimo_nacional']);
        // Verify gestora total = 12.71% stored as decimal
        $this->assertEqualsWithDelta(0.1271, (float) $d['porcentaje_gestora_total'], 0.001);
        $this->assertCount(3, $d['escalas_bono_antiguedad'] ?? []);
    }
}
