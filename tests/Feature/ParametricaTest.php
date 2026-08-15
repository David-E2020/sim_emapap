<?php

namespace Tests\Feature;

use App\Models\Parametrica;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ParametricaTest extends TestCase
{
    protected $token;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test de listar tablas maestras paramétricas.
     */
    public function test_listar_tablas_parametricas()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/parametrica-api');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    /**
     * Test de creación de tabla paramétrica origen y registro de campos hijos.
     */
    public function test_crear_tabla_y_campo_parametrico()
    {
        $tablaNombre = 'TABLA_TEST_' . time();

        // 1. Crear origen de paramétrica
        $responseOrigen = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/parametrica-api', [
                'param_tabla' => $tablaNombre,
                'param_nombre' => 'TABLA DE PRUEBAS',
                'param_descripcion' => 'Descripción de prueba',
            ]);

        $responseOrigen->assertStatus(200)
            ->assertJson([
                'success' => 'true',
            ]);

        // 2. Registrar campo hijo en la tabla
        $responseCampo = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/registrar_campo', [
                'param_tabla' => $tablaNombre,
                'param_nombre' => 'VALOR PRUEBA 1',
                'param_codigo' => 'VP1',
                'param_detalle' => 'Detalle del valor',
            ]);

        $responseCampo->assertStatus(200)
            ->assertJson([
                'success' => 'true',
            ]);

        // 3. Consultar campos de la tabla creada
        $responseShow = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/parametrica-api/' . $tablaNombre);

        $responseShow->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($responseShow->json()));

        // 4. Limpieza lógica
        $campoHijo = Parametrica::where('param_tabla', $tablaNombre)->where('param_valor', '>', 0)->first();
        if ($campoHijo) {
            $responseDelete = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                ->deleteJson('/api/parametrica-api/' . $campoHijo->id);

            $responseDelete->assertStatus(200)
                ->assertJson([
                    'success' => 'true',
                ]);
        }
    }
}
