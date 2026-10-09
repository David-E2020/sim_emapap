<?php

declare(strict_types=1);

namespace Tests\Feature\Rrhh;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PlanillaDeclararReabrirTest extends TestCase
{
    protected string $token;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    public function test_ciclo_completo_declarar_y_reabrir_planilla(): void
    {
        $mes = 11;
        $anio = 2026;
        $tipo = 'PLANTA_PERMANENTE';

        // 1. Simulación previa
        $resPrevia = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/rrhh/reportes/planilla-sueldos?mes={$mes}&anio={$anio}&tipo_planilla={$tipo}");
        $resPrevia->assertStatus(200)->assertJson(['success' => true]);

        // Asegurar que esté limpia
        DB::table('rrhh.planillas_consolidadas')
            ->where('gestion', $anio)
            ->where('mes', $mes)
            ->where('tipo_planilla', $tipo)
            ->delete();

        // 2. Declarar planilla
        $resDeclarar = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/rrhh/reportes/planilla-sueldos/declarar', [
                'mes' => $mes,
                'anio' => $anio,
                'tipo_planilla' => $tipo,
            ]);
        $resDeclarar->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
        $this->assertNotEmpty($resDeclarar->json('cite_oficial'));

        $this->assertDatabaseHas('rrhh.planillas_consolidadas', [
            'gestion' => $anio,
            'mes' => $mes,
            'tipo_planilla' => $tipo,
            'estado' => 'DECLARADA',
        ]);

        // 3. Reabrir planilla
        $resReabrir = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/rrhh/reportes/planilla-sueldos/reabrir', [
                'mes' => $mes,
                'anio' => $anio,
                'tipo_planilla' => $tipo,
            ]);
        $resReabrir->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('rrhh.planillas_consolidadas', [
            'gestion' => $anio,
            'mes' => $mes,
            'tipo_planilla' => $tipo,
        ]);
    }
}
