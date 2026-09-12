<?php

declare(strict_types=1);

namespace Tests\Feature\Rrhh;

use App\Models\Rrhh\Justificacion;
use App\Models\Rrhh\Permiso;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\SolicitudSalida;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RrhhInteractiveJourneyTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;
    protected string $token;
    protected Persona $persona;
    protected Permiso $permiso;
    protected Justificacion $justificacion;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->admin);

        $this->persona = Persona::where('_estado', 'ACTIVO')->first()
            ?? Persona::create([
                'nombres' => 'ROBERTO CARLOS',
                'primer_apellido' => 'MAMANI',
                'segundo_apellido' => 'CONDORI',
                'nro_documento' => '5987123',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]);

        $this->permiso = Permiso::where('_estado', 'ACTIVO')->first()
            ?? Permiso::create([
                'nombre' => 'PERMISO PARTICULAR',
                'sigla' => 'PP',
                'tiempo_maximo' => 2,
                'tipo_tiempo' => 'HORAS',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]);

        $this->justificacion = Justificacion::where('_estado', 'ACTIVO')->first()
            ?? Justificacion::create([
                'nombre' => 'TRAMITE PERSONAL O FAMILIAR',
                'sigla' => 'TPF',
                'descripcion' => 'Diligencias personales debidamente justificadas',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
            ]);
    }

    /**
     * Paso 1: Obtención del catálogo oficial de permisos y justificaciones
     */
    public function test_catalogo_permisos_y_justificaciones(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/permisos/catalogo');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'permisos',
                'justificaciones',
            ]);

        $this->assertNotEmpty($response->json('permisos'));
        $this->assertNotEmpty($response->json('justificaciones'));
    }

    /**
     * Paso 2: Creación exitosa de boleta de salida / solicitud de permiso con CITE atómico
     */
    public function test_creacion_boleta_salida_permiso(): void
    {
        $payload = [
            'id_persona' => $this->persona->id,
            'id_permiso' => $this->permiso->id,
            'id_justificacion' => $this->justificacion->id,
            'motivo' => 'Trámite personal bancario y gestión notarial urgente',
            'fecha_inicio' => '2026-09-15',
            'fecha_fin' => '2026-09-15',
            'hora_inicio' => '09:00',
            'hora_fin' => '11:00',
            'horas_solicitadas' => 2.0,
        ];

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/rrhh/solicitudes', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Boleta de salida registrada exitosamente.',
            ]);

        $solicitudId = $response->json('data.id');
        $cite = $response->json('data.cite');

        $this->assertNotNull($solicitudId);
        $this->assertStringStartsWith('CITE-RRHH-2026-', $cite);

        // Verificar persistencia en base de datos
        $this->assertDatabaseHas('rrhh.solicitudes_salidas', [
            'id' => $solicitudId,
            'id_permiso' => $this->permiso->id,
            'cite' => $cite,
        ]);

        $this->assertDatabaseHas('rrhh.usuarios_solicitudes_salidas', [
            'id_solicitud_salida' => $solicitudId,
            'id_persona' => $this->persona->id,
            'estado_aprobacion' => 'PENDIENTE',
        ]);
    }

    /**
     * Paso 3: Listar solicitudes y verificar aparición en bandeja de aprobaciones
     */
    public function test_listar_solicitudes_y_bandeja_aprobaciones(): void
    {
        // Crear una solicitud
        $solicitud = SolicitudSalida::create([
            'id_permiso' => $this->permiso->id,
            'id_justificacion' => $this->justificacion->id,
            'motivo' => 'Consulta médica especialista',
            'fecha_inicio' => '2026-09-16',
            'fecha_fin' => '2026-09-16',
            'hora_inicio' => '14:00',
            'hora_fin' => '16:00',
            'horas_solicitadas' => 2,
            'cite' => 'CITE-RRHH-2026-TESTMED',
            '_usuario_creacion' => $this->admin->id,
            '_fecha_creacion' => now(),
        ]);

        DB::table('rrhh.usuarios_solicitudes_salidas')->insert([
            'id_solicitud_salida' => $solicitud->id,
            'id_persona' => $this->persona->id,
            'estado_aprobacion' => 'PENDIENTE',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => $this->admin->id,
            '_fecha_creacion' => now(),
        ]);

        // Listar en bandeja general
        $respList = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/solicitudes');

        $respList->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verificar en bandeja de aprobaciones para jefatura/supervisores
        $respAprob = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/rrhh/bandeja-aprobaciones');

        $respAprob->assertStatus(200)
            ->assertJson(['success' => true]);

        $pendientes = collect($respAprob->json('data'));
        $encontrado = $pendientes->firstWhere('cite', 'CITE-RRHH-2026-TESTMED');
        $this->assertNotNull($encontrado, 'La solicitud debe aparecer en la bandeja de aprobaciones.');
        $this->assertEquals('PENDIENTE', $encontrado->estado_aprobacion ?? $encontrado['estado_aprobacion']);
    }

    /**
     * Paso 4: Flujo de aprobación oficial por jefatura
     */
    public function test_aprobacion_de_solicitud_en_bandeja(): void
    {
        $solicitud = SolicitudSalida::create([
            'id_permiso' => $this->permiso->id,
            'id_justificacion' => $this->justificacion->id,
            'motivo' => 'Asistencia a seminario técnico institucional',
            'fecha_inicio' => '2026-09-18',
            'fecha_fin' => '2026-09-18',
            'hora_inicio' => '08:30',
            'hora_fin' => '12:30',
            'horas_solicitadas' => 4,
            'cite' => 'CITE-RRHH-2026-CAPACITACION',
            '_usuario_creacion' => $this->admin->id,
            '_fecha_creacion' => now(),
        ]);

        DB::table('rrhh.usuarios_solicitudes_salidas')->insert([
            'id_solicitud_salida' => $solicitud->id,
            'id_persona' => $this->persona->id,
            'estado_aprobacion' => 'PENDIENTE',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => $this->admin->id,
            '_fecha_creacion' => now(),
        ]);

        // Resolver aprobando la solicitud
        $respResolver = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->putJson("/api/rrhh/bandeja-aprobaciones/{$solicitud->id}/resolver", [
                'estado' => 'APROBADO',
                'observacion' => 'Aprobado conforme al reglamento interno de personal EMAPA',
            ]);

        $respResolver->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Solicitud APROBADO con éxito.',
            ]);

        // Verificar actualización en base de datos
        $this->assertDatabaseHas('rrhh.usuarios_solicitudes_salidas', [
            'id_solicitud_salida' => $solicitud->id,
            'estado_aprobacion' => 'APROBADO',
            'observacion' => 'Aprobado conforme al reglamento interno de personal EMAPA',
        ]);

        $this->assertDatabaseHas('rrhh.solicitudes_salidas', [
            'id' => $solicitud->id,
            '_estado' => 'APROBADO',
        ]);
    }

    /**
     * Paso 5: Generación de reporte imprimible de la boleta de salida
     */
    public function test_reporte_imprimible_boleta_salida(): void
    {
        $solicitud = SolicitudSalida::create([
            'id_permiso' => $this->permiso->id,
            'id_justificacion' => $this->justificacion->id,
            'motivo' => 'Comisión de soporte técnico en planta',
            'fecha_inicio' => '2026-09-20',
            'fecha_fin' => '2026-09-20',
            'hora_inicio' => '09:00',
            'hora_fin' => '13:00',
            'horas_solicitadas' => 4,
            'cite' => 'CITE-RRHH-2026-BOLETASOPORTE',
            '_usuario_creacion' => $this->admin->id,
            '_fecha_creacion' => now(),
        ]);

        DB::table('rrhh.usuarios_solicitudes_salidas')->insert([
            'id_solicitud_salida' => $solicitud->id,
            'id_persona' => $this->persona->id,
            'estado_aprobacion' => 'PENDIENTE',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => $this->admin->id,
            '_fecha_creacion' => now(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/rrhh/reportes/boleta-salida/{$solicitud->id}/html");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'solicitud',
                    'funcionario',
                    'fecha_emision',
                    'institucion',
                ],
            ]);

        $this->assertStringContainsString('EMAPA', $response->json('data.institucion'));
    }
}
