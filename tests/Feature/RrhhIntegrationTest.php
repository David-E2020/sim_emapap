<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rrhh\Biometrico;
use App\Models\Rrhh\Horario;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RrhhIntegrationTest extends TestCase
{
    public function test_rrhh_schema_and_catalogs_are_present(): void
    {
        $departamentosCount = DB::table('rrhh.departamentos')->count();
        $this->assertGreaterThanOrEqual(9, $departamentosCount);

        $feriadosCount = DB::table('rrhh.feriados')->count();
        $this->assertGreaterThanOrEqual(7, $feriadosCount);

        $permisosCount = DB::table('rrhh.permisos')->count();
        $this->assertGreaterThanOrEqual(5, $permisosCount);
    }

    public function test_can_create_persona_and_attendance_flow(): void
    {
        $ciTest = 'CI-' . uniqid();
        $persona = Persona::create([
            'nombres' => 'JUAN CARLOS',
            'primer_apellido' => 'PEREZ',
            'segundo_apellido' => 'MAMANI',
            'nro_documento' => $ciTest,
            'telefono_celular' => '71234567',
            'correo_electronico_personal' => 'juan.perez.' . uniqid() . '@emapa.gob.bo',
            'genero' => 'MASCULINO',
        ]);

        $this->assertNotNull($persona->id);
        $this->assertEquals('JUAN CARLOS PEREZ MAMANI', $persona->nombre_completo);

        // Crear Reloj Biométrico
        $biometrico = Biometrico::create([
            'nombre' => 'BIOMETRICO CENTRAL TEST',
            'url' => '192.168.1.250',
            'puerto' => 4370,
            'tipo' => 'ZKTECO',
            'ubicacion' => 'Ingreso Test',
        ]);

        $this->assertNotNull($biometrico->id);

        // Crear Horario y Turnos
        $horario = Horario::create([
            'nombre' => 'HORARIO TEST TEMPORAL',
            'tipo' => 'CONTINUO',
            'tolerancia_minutos' => 10,
        ]);

        $this->assertNotNull($horario->id);

        // Registrar asistencia
        $asistencia = $persona->asistencias()->create([
            'fecha' => now()->format('Y-m-d'),
            'entrada_primer_periodo' => '08:05',
            'salida_primer_periodo' => '16:00',
            'minutos_de_atraso_primer_periodo' => 0,
            'merece_refrigerio' => true,
            'id_horario' => $horario->id,
        ]);

        $this->assertNotNull($asistencia->id);
        $this->assertTrue($asistencia->merece_refrigerio);

        // Cleanup test data
        $asistencia->delete();
        $horario->delete();
        $biometrico->delete();
        $persona->delete();
    }

    public function test_can_create_organizational_structure_and_job_positions(): void
    {
        $unidad = UnidadOrganizacional::create([
            'nombre' => 'UNIDAD TEMPORAL DE PRUEBA TEST',
            'sigla' => 'UTEST',
            'es_unidad_recursos_humanos' => false,
        ]);

        $this->assertNotNull($unidad->id);

        $puesto = Puesto::create([
            'nombre' => 'PUESTO DE PRUEBA TEST',
            'tipo_puesto' => 'PLANTA',
            'id_unidad_organizacional' => $unidad->id,
        ]);

        $this->assertNotNull($puesto->id);
        $this->assertEquals('UTEST', $puesto->unidadOrganizacional->sigla);

        // Cleanup test data
        $puesto->delete();
        $unidad->delete();
    }
}
