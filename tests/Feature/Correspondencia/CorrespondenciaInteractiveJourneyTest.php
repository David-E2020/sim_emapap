<?php

declare(strict_types=1);

namespace Tests\Feature\Correspondencia;

use App\Models\Correspondencia\HojaRuta;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CorrespondenciaInteractiveJourneyTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected string $token;
    protected UnidadOrganizacional $unidadOrigen;
    protected UnidadOrganizacional $unidadDestino;
    protected Persona $personaOrigen;
    protected Persona $personaDestino;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->admin);

        $this->unidadOrigen = UnidadOrganizacional::where('_estado', 'ACTIVO')->first()
            ?? UnidadOrganizacional::create([
                'nombre' => 'UNIDAD DE TECNOLOGÍAS Y SISTEMAS',
                'sigla' => 'UTIC',
                'nivel_jerarquico' => 2,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]);

        $this->unidadDestino = UnidadOrganizacional::where('_estado', 'ACTIVO')
            ->where('id', '!=', $this->unidadOrigen->id)
            ->first()
            ?? UnidadOrganizacional::create([
                'nombre' => 'GERENCIA GENERAL',
                'sigla' => 'GG',
                'nivel_jerarquico' => 1,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]);

        $this->personaOrigen = Persona::first() ?? Persona::create([
            'nombre' => 'JUAN',
            'apellido_paterno' => 'PEREZ',
            'apellido_materno' => 'ORTEGA',
            'numero_documento' => '4567890',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'TEST',
        ]);

        $this->personaDestino = Persona::where('id', '!=', $this->personaOrigen->id)->first()
            ?? Persona::create([
                'nombre' => 'MARIA',
                'apellido_paterno' => 'QUISPE',
                'apellido_materno' => 'CONDORI',
                'numero_documento' => '7890123',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]);
    }

    protected function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_ciclo_completo_correspondencia_hoja_ruta(): void
    {
        // PASO 1: Consultar bandeja de entrada y salida
        $resBandeja = $this->withHeaders($this->headers())
            ->getJson('/api/correspondencia/hojas-ruta?bandeja=ENTRADA');

        $resBandeja->assertStatus(200)
            ->assertJson(['success' => true]);

        // PASO 2: Crear nueva Hoja de Ruta con derivación inmediata
        $asunto = 'SOLICITUD DE AUDITORIA Y MANTENIMIENTO PREVENTIVO RED DE AGUA';
        $resCrear = $this->withHeaders($this->headers())
            ->postJson('/api/correspondencia/hojas-ruta', [
                'asunto' => $asunto,
                'prioridad' => 'ALTA',
                'tipo_hr' => 'INTERNA',
                'nro_fojas' => 5,
                'nro_anexos' => 1,
                'id_unidad_origen' => $this->unidadOrigen->id,
                'id_persona_origen' => $this->personaOrigen->id,
                'proveido' => 'PASE A SUS EFECTOS',
                'instruccion_detalle' => 'Revisión técnica inmediata y respuesta en plazo legal',
                'destinatarios' => [
                    [
                        'id_unidad_destino' => $this->unidadDestino->id,
                        'id_funcionario_destino' => $this->personaDestino->id,
                        'es_copia' => false,
                    ],
                ],
            ]);

        $resCrear->assertStatus(201)
            ->assertJson(['success' => true]);

        $hrData = $resCrear->json('data');
        $this->assertNotNull($hrData);
        $hrId = $hrData['id'];
        $cite = $hrData['nro_hoja_ruta'];

        $this->assertNotEmpty($cite);
        $this->assertStringContainsString('HR-', $cite);
        $this->assertEquals('ALTA', $hrData['prioridad']);

        // PASO 3: Consultar detalle de la Hoja de Ruta por ID
        $resShow = $this->withHeaders($this->headers())
            ->getJson('/api/correspondencia/hojas-ruta/' . $hrId);

        $resShow->assertStatus(200)
            ->assertJson(['success' => true]);
        $this->assertEquals($asunto, $resShow->json('data.asunto'));

        // PASO 4: Consultar línea de tiempo y trazabilidad (Timeline)
        $resTimeline = $this->withHeaders($this->headers())
            ->getJson('/api/correspondencia/seguimiento/' . $cite . '/timeline');

        $resTimeline->assertStatus(200)
            ->assertJson(['success' => true]);

        $timelineData = $resTimeline->json('data');
        $this->assertNotNull($timelineData);
        $this->assertNotEmpty($timelineData['timeline']);

        // PASO 5: Generar carátula oficial con QR
        $resCaratula = $this->withHeaders($this->headers())
            ->getJson('/api/correspondencia/hojas-ruta/' . $hrId . '/caratula');

        $resCaratula->assertStatus(200);

        // PASO 6: Concluir y archivar trámite
        $resCerrar = $this->withHeaders($this->headers())
            ->postJson('/api/correspondencia/hojas-ruta/' . $hrId . '/cerrar', [
                'motivo_cierre' => 'Trámite concluido a satisfacción según evaluación técnica',
            ]);

        $resCerrar->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verificar cambio de estado en base de datos
        $hrDb = HojaRuta::find($hrId);
        $this->assertEquals('CERRADO', $hrDb->estado);
    }
}
