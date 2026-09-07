<?php

namespace Tests\Feature\Datos;

use App\Models\Facturacion\ConfiguracionEmpresa;
use App\Models\User;
use App\Services\Facturacion\SiatSoapService;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ConfiguracionEmpresaSiatTest extends TestCase
{
    protected $token;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first();
        if (!$this->admin) {
            $this->admin = User::first();
        }
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test de obtención de configuración institucional y SIAT activa.
     */
    public function test_obtener_configuracion_empresa_activa()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/datos/empresa');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertNotNull($data);
        $this->assertArrayHasKey('razon_social', $data);
        $this->assertArrayHasKey('nit', $data);
        $this->assertArrayHasKey('codigo_ambiente', $data);
        $this->assertArrayHasKey('codigo_modalidad', $data);
        $this->assertArrayHasKey('codigo_sistema', $data);
        $this->assertArrayHasKey('token_delegado', $data);
        $this->assertArrayHasKey('tiene_certificado', $data);
        $this->assertArrayHasKey('tiene_password', $data);
    }

    /**
     * Test de actualización de configuración institucional y parámetros SIAT.
     */
    public function test_guardar_actualizacion_parametros_siat()
    {
        $testToken = 'TOKEN_TEST_' . uniqid();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/empresa', [
                'razon_social' => 'EMAPAP - TEST PATACAMAYA',
                'nombre_comercial' => 'EMAPAP TEST',
                'nit' => '1000000025',
                'telefono' => '22830000',
                'correo' => 'test@emapap.gob.bo',
                'direccion' => 'Plaza Principal Patacamaya',
                'municipio' => 'Patacamaya',
                'codigo_ambiente' => 1,
                'codigo_modalidad' => 1,
                'codigo_sistema' => 'TEST_SISTEMA_CODE',
                'token_delegado' => $testToken,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verificar persistencia en base de datos
        $configActiva = ConfiguracionEmpresa::getActiva();
        $this->assertEquals('EMAPAP - TEST PATACAMAYA', $configActiva->razon_social);
        $this->assertEquals('1000000025', $configActiva->nit);
        $this->assertEquals(1, $configActiva->codigo_ambiente);
        $this->assertEquals(1, $configActiva->codigo_modalidad);
        $this->assertEquals('TEST_SISTEMA_CODE', $configActiva->codigo_sistema);
        $this->assertEquals($testToken, $configActiva->token_delegado);
    }

    /**
     * Test del endpoint de diagnóstico y ping de conectividad con el SIN.
     */
    public function test_probar_conexion_siat_endpoint()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/empresa/probar-conexion', [
                'codigo_ambiente' => 1,
                'token_delegado' => 'TEST_TOKEN',
            ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('ambiente', $data);
        $this->assertArrayHasKey('tiempo_respuesta_ms', $data);
        $this->assertArrayHasKey('endpoint', $data);
        $this->assertEquals(1, $data['ambiente']);
    }

    /**
     * Test de que los servicios de facturación utilizan los valores dinámicos configurados.
     */
    public function test_servicios_facturacion_utilizan_configuracion_dinamica()
    {
        $config = ConfiguracionEmpresa::getActiva();

        $soapService = app(SiatSoapService::class);
        $reflection = new \ReflectionClass($soapService);

        $propNit = $reflection->getProperty('nitEmisor');
        $propNit->setAccessible(true);
        $this->assertEquals($config->nit, $propNit->getValue($soapService));

        $propAmbiente = $reflection->getProperty('ambiente');
        $propAmbiente->setAccessible(true);
        $this->assertEquals($config->codigo_ambiente, $propAmbiente->getValue($soapService));

        $propModalidad = $reflection->getProperty('modalidad');
        $propModalidad->setAccessible(true);
        $this->assertEquals($config->codigo_modalidad, $propModalidad->getValue($soapService));

        $propCodigoSistema = $reflection->getProperty('codigoSistema');
        $propCodigoSistema->setAccessible(true);
        $this->assertEquals($config->codigo_sistema, $propCodigoSistema->getValue($soapService));
    }
}
