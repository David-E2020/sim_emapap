<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rrhh\Persona;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RrhhCompleteVerificationTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::first() ?: User::factory()->create();
        $this->token = JWTAuth::fromUser($user);
    }

    public function test_personal_and_organigrama_endpoints(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/personal');
        $response->assertStatus(200)->assertJson(['success' => true]);

        $responseOrg = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/organigrama');
        $responseOrg->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_horarios_and_asignaciones_endpoints(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/horarios');
        $response->assertStatus(200)->assertJson(['success' => true]);

        $responseAsig = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/asignaciones-horarios');
        $responseAsig->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_comisiones_omisiones_and_bandeja_endpoints(): void
    {
        $resCom = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/comisiones');
        $resCom->assertStatus(200)->assertJson(['success' => true]);

        $resOmi = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/omisiones');
        $resOmi->assertStatus(200)->assertJson(['success' => true]);

        $resBandeja = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/bandeja-aprobaciones');
        $resBandeja->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_feriados_and_fechas_corte_endpoints(): void
    {
        $resFeriados = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/feriados?anio=2026');
        $resFeriados->assertStatus(200)->assertJson(['success' => true]);

        $resCortes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/fechas-corte');
        $resCortes->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_reportes_oficiales_endpoints(): void
    {
        $resAsistencia = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/reportes/asistencia-mensual?mes=8&anio=2026');
        $resAsistencia->assertStatus(200)->assertJson(['success' => true]);

        $resRefrigerio = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/reportes/refrigerio-mensual?mes=8&anio=2026');
        $resRefrigerio->assertStatus(200)->assertJson(['success' => true]);

        $resVacaciones = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/reportes/saldo-vacaciones');
        $resVacaciones->assertStatus(200)->assertJson(['success' => true]);

        $resPlanillaSueldos = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/reportes/planilla-sueldos?mes=8&anio=2026');
        $resPlanillaSueldos->assertStatus(200)->assertJson(['success' => true]);
    }
}
