<?php

declare(strict_types=1);

namespace Tests\Feature\Rrhh;

use App\Models\Rrhh\ConfiguracionLaboral;
use App\Models\Rrhh\Persona;
use App\Models\User;
use App\Services\Rrhh\PlanillaExcelImportService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PlanillaProcesamientoIntegralTest extends TestCase
{
    protected string $token;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test 1: Configuración laboral vigente con SMN 3300 Bs y aportes de ley Bolivia.
     */
    public function test_configuracion_laboral_vigente_parametros_bolivia(): void
    {
        $config = ConfiguracionLaboral::obtenerVigente(2026);
        $this->assertNotNull($config);
        $this->assertEquals(3300.0, (float) $config->salario_minimo_nacional);
        
        $gestora = (float) $config->porcentaje_gestora_total;
        $this->assertTrue(in_array(round($gestora, 4), [0.1271, 12.71]));

        $cns = (float) $config->porcentaje_patronal_cns;
        $this->assertTrue(in_array(round($cns, 4), [0.10, 10.0]));

        $patGestora = (float) $config->porcentaje_patronal_gestora_total;
        $this->assertTrue(in_array(round($patGestora, 4), [0.0721, 7.21]));

        $this->assertEquals(3, (int) $config->multiplicador_smn_antiguedad);
    }

    /**
     * Test 2: Sincronización del personal institucional (14 funcionarios en total).
     */
    public function test_personal_institucional_total_14_funcionarios(): void
    {
        $personas = Persona::with('asignacionesPuestos')->get();
        $this->assertGreaterThanOrEqual(14, $personas->count());

        $planta = $personas->filter(fn($p) => str_starts_with($p->asignacionesPuestos->first()?->asignacion ?? '', 'P-'));
        $eventual = $personas->filter(fn($p) => str_starts_with($p->asignacionesPuestos->first()?->asignacion ?? '', 'E-'));
        $directorio = $personas->filter(fn($p) => str_starts_with($p->asignacionesPuestos->first()?->asignacion ?? '', 'D-'));

        $this->assertCount(10, $planta, 'Deben existir 10 funcionarios en Planta Permanente');
        $this->assertCount(1, $eventual, 'Debe existir 1 funcionario en Personal Eventual');
        $this->assertCount(3, $directorio, 'Deben existir 3 miembros de Directorio');
    }

    /**
     * Test 3: Previa de simulación con tipo_planilla = TODAS devuelve los 14 funcionarios y totales exactos.
     */
    public function test_previa_simulacion_tipo_planilla_todas(): void
    {
        // Limpiar cualquier planilla previa para Septiembre 2026
        DB::table('rrhh.detalles_planillas')->whereIn(
            'id_planilla_consolidada',
            DB::table('rrhh.planillas_consolidadas')->where('gestion', 2026)->where('mes', 9)->pluck('id')
        )->delete();
        DB::table('rrhh.planillas_consolidadas')->where('gestion', 2026)->where('mes', 9)->delete();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/reportes/planilla-sueldos?mes=9&anio=2026&tipo_planilla=TODAS');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertCount(14, $data);

        // Totales consolidados institucionales
        $this->assertEquals(44380.80, (float) $response->json('total_ganado_bs'));
        $this->assertEquals(4992.60, (float) $response->json('total_descuentos_bs'));
        $this->assertEquals(39388.20, (float) $response->json('total_liquido_salarial_bs'));
    }

    /**
     * Test 4: Generación en lote con opción TODAS crea las 3 planillas simultáneamente como BORRADOR.
     */
    public function test_generar_planillas_en_lote_como_borrador(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/rrhh/reportes/planillas/generar', [
                'mes' => 9,
                'anio' => 2026,
                'tipo_planilla' => 'TODAS',
                'estado' => 'BORRADOR',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'estado' => 'BORRADOR',
                'total_funcionarios' => 14,
            ]);

        $this->assertDatabaseHas('rrhh.planillas_consolidadas', [
            'gestion' => 2026,
            'mes' => 9,
            'tipo_planilla' => 'PLANTA_PERMANENTE',
            'estado' => 'BORRADOR',
        ]);

        $this->assertDatabaseHas('rrhh.planillas_consolidadas', [
            'gestion' => 2026,
            'mes' => 9,
            'tipo_planilla' => 'PERSONAL_EVENTUAL',
            'estado' => 'BORRADOR',
        ]);

        $this->assertDatabaseHas('rrhh.planillas_consolidadas', [
            'gestion' => 2026,
            'mes' => 9,
            'tipo_planilla' => 'DIETAS_DIRECTORIO',
            'estado' => 'BORRADOR',
        ]);

        // Verificar cantidad de registros en detalles
        $ids = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', 2026)
            ->where('mes', 9)
            ->pluck('id');
        $totalDetalles = DB::table('rrhh.detalles_planillas')->whereIn('id_planilla_consolidada', $ids)->count();
        $this->assertEquals(14, $totalDetalles);
    }

    /**
     * Test 5: Consolidar y declarar en lote con opción TODAS asigna CITEs oficiales inmutables.
     */
    public function test_consolidar_y_declarar_en_lote_todas(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/rrhh/reportes/planillas/generar', [
                'mes' => 9,
                'anio' => 2026,
                'tipo_planilla' => 'TODAS',
                'estado' => 'DECLARADA',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'estado' => 'DECLARADA',
                'total_funcionarios' => 14,
            ]);

        $planillas = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', 2026)
            ->where('mes', 9)
            ->get();

        $this->assertCount(3, $planillas);
        foreach ($planillas as $p) {
            $this->assertEquals('DECLARADA', $p->estado);
            $this->assertNotNull($p->cite_oficial);
            $this->assertStringContainsString('PLA-EMAPA-', $p->cite_oficial);
        }
    }

    /**
     * Test 6: Reabrir planillas en lote con opción TODAS regresa todo a modo simulación.
     */
    public function test_reabrir_planillas_en_lote_todas(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/rrhh/reportes/planilla-sueldos/reabrir', [
                'mes' => 9,
                'anio' => 2026,
                'tipo_planilla' => 'TODAS',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $count = DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', 2026)
            ->where('mes', 9)
            ->count();
        $this->assertEquals(0, $count);
    }

    /**
     * Test 7: Exportación oficial de PDFs (Boleta Individual, Boletas Masivas y Planilla Sueldos).
     */
    public function test_emision_pdfs_oficiales_boletas_y_planillas(): void
    {
        $persona = Persona::first();
        $this->assertNotNull($persona);

        // Boleta individual
        $resBoleta = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get("/api/rrhh/reportes/boleta-pago/{$persona->id}/pdf?mes=9&anio=2026");
        $this->assertTrue(in_array($resBoleta->status(), [200, 302]));

        // Planilla sueldos PDF
        $resPlanilla = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get("/api/rrhh/reportes/planilla-sueldos/pdf?mes=9&anio=2026&tipo_planilla=PLANTA_PERMANENTE");
        $this->assertTrue(in_array($resPlanilla->status(), [200, 302]));

        // Boletas masivas PDF
        $resMasivas = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->get('/api/rrhh/reportes/boletas-pago/masivas/pdf?mes=9&anio=2026&tipo_planilla=TODAS');
        $this->assertTrue(in_array($resMasivas->status(), [200, 302]));
    }
}
