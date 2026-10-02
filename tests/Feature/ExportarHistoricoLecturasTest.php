<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\User;
use App\Services\Comercial\ExportarHistoricoLecturasService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExportarHistoricoLecturasTest extends TestCase
{
    use DatabaseTransactions;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = User::where('usr_usuario', 'admin')->first() ?: User::first();
        if ($admin) {
            $this->token = JWTAuth::fromUser($admin);
        }
    }

    public function test_periodos_disponibles_retorna_lista(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/comercial/periodos/exportar-historico/periodos-disponibles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'periodos',
                'zonas',
                'categorias',
            ]);
    }

    public function test_iniciar_exportacion_crea_job(): void
    {
        $periodo = PeriodoFacturacion::firstOrCreate(
            ['periodo' => '09/2026'],
            [
                'mes' => 9,
                'gestion' => 2026,
                'fecha_inicio_consumo' => '2026-09-01',
                'fecha_fin_consumo' => '2026-09-30',
                'fecha_vencimiento_pago' => '2026-10-15',
                'estado' => 'FACTURACION',
            ]
        );

        $payload = [
            'tipo_rango' => 'manual',
            'periodo_ids' => [$periodo->id],
            'formato' => 'csv',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/comercial/periodos/exportar-historico/iniciar', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'total_periodos' => 1,
                'formato' => 'csv',
            ]);

        $jobId = $response->json('job_id');
        $this->assertNotEmpty($jobId);

        // Consultar estado
        $resEstado = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/comercial/periodos/exportar-historico/{$jobId}/estado");

        $resEstado->assertStatus(200);

        // Ejecutar servicio directamente para probar generación
        $service = app(ExportarHistoricoLecturasService::class);
        $service->procesarJob($jobId);

        // Verificar estado completado
        $resEstadoFinal = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/comercial/periodos/exportar-historico/{$jobId}/estado");

        $resEstadoFinal->assertStatus(200)
            ->assertJson([
                'estado' => 'COMPLETADO',
                'progreso' => 100,
            ]);

        // Probar endpoint de descarga
        $resDescarga = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->get("/api/comercial/periodos/exportar-historico/{$jobId}/descargar");

        $resDescarga->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $resDescarga->headers->get('content-type'));

        // Limpieza de archivos temporales de prueba
        @unlink(storage_path("app/reportes_jobs/{$jobId}.json"));
        @unlink(storage_path("app/reportes_jobs/" . $resEstadoFinal->json('nombre_archivo')));
    }

    public function test_exportacion_con_columnas_seleccionadas(): void
    {
        $periodo = PeriodoFacturacion::firstOrCreate(
            ['periodo' => '09/2026'],
            [
                'mes' => 9,
                'gestion' => 2026,
                'fecha_inicio_consumo' => '2026-09-01',
                'fecha_fin_consumo' => '2026-09-30',
                'fecha_vencimiento_pago' => '2026-10-15',
                'estado' => 'FACTURACION',
            ]
        );

        $payload = [
            'tipo_rango' => 'manual',
            'periodo_ids' => [$periodo->id],
            'formato' => 'csv',
            'columnas' => ['codigo', 'nombre_completo', 'total_facturado'],
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/comercial/periodos/exportar-historico/iniciar', $payload);

        $response->assertStatus(200);
        $jobId = $response->json('job_id');

        $service = app(ExportarHistoricoLecturasService::class);
        $service->procesarJob($jobId);

        $jobData = json_decode(file_get_contents(storage_path("app/reportes_jobs/{$jobId}.json")), true);
        $this->assertEquals('COMPLETADO', $jobData['estado']);

        $csvFile = storage_path("app/reportes_jobs/" . $jobData['nombre_archivo']);
        $this->assertFileExists($csvFile);

        $linea1 = fgets(fopen($csvFile, 'r'));
        $this->assertStringContainsString('Código Socio', $linea1);
        $this->assertStringContainsString('Nombre Abonado', $linea1);
        $this->assertStringContainsString('Total Facturado', $linea1);
        // Verificar que no contenga columnas deseleccionadas
        $this->assertStringNotContainsString('N° Serie Medidor', $linea1);
        $this->assertStringNotContainsString('Alcantarillado', $linea1);

        @unlink(storage_path("app/reportes_jobs/{$jobId}.json"));
        @unlink($csvFile);
    }
}
