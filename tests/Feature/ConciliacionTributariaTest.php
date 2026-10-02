<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ConciliacionTributariaTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(1) ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_archivos_sin_disponibles_retorna_lista_exitosa(): void
    {
        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->getJson('/api/facturacion/reportes/libro-ventas/archivos-sin');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);
    }

    public function test_conciliar_sin_con_archivo_valido_retorna_kpis(): void
    {
        $rutaArchivo = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/archivoVentas.xlsx';
        if (!file_exists($rutaArchivo)) {
            $this->markTestSkipped('Archivo archivoVentas.xlsx no encontrado para pruebas.');
        }

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson('/api/facturacion/reportes/libro-ventas/conciliar-sin', [
                'ruta_archivo' => $rutaArchivo,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'archivo',
                'periodo' => ['desde', 'hasta'],
                'kpis' => [
                    'sin' => ['total_registros', 'total_validas', 'total_anuladas', 'total_facturado', 'total_debito_fiscal'],
                    'sistema' => ['total_registros', 'total_validas', 'total_anuladas'],
                    'resumen_cotejo' => ['coincidentes_exactas', 'anuladas_faltantes', 'diferencias_ley1886'],
                ],
                'detalles' => [
                    'anuladas_faltantes',
                    'diferencias_ley1886',
                    'coincidentes_muestra',
                ],
            ]);
    }

    public function test_sincronizar_sin_regulariza_estados(): void
    {
        $rutaArchivo = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/archivoVentas.xlsx';
        if (!file_exists($rutaArchivo)) {
            $this->markTestSkipped('Archivo archivoVentas.xlsx no encontrado para pruebas.');
        }

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson('/api/facturacion/reportes/libro-ventas/sincronizar-sin', [
                'ruta_archivo' => $rutaArchivo,
                'importar_anuladas' => true,
                'regularizar_ley1886' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'mensaje',
                'anuladas_insertadas',
                'descuentos_actualizados',
                'nuevos_totales',
            ]);
    }

    public function test_conciliar_sin_con_rango_fechas_personalizado(): void
    {
        $rutaArchivo = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/archivoVentas.xlsx';
        if (!file_exists($rutaArchivo)) {
            $this->markTestSkipped('Archivo archivoVentas.xlsx no encontrado para pruebas.');
        }

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->postJson('/api/facturacion/reportes/libro-ventas/conciliar-sin', [
                'ruta_archivo' => $rutaArchivo,
                'fecha_desde' => '2026-08-01',
                'fecha_hasta' => '2026-08-15',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'periodo' => [
                    'desde' => '2026-08-01',
                    'hasta' => '2026-08-15',
                ],
            ]);

        $kpis = $response->json('kpis');
        // El número de registros en la primera quincena debe ser mayor a 0 y menor al total de todo el mes (4,676)
        $this->assertGreaterThan(0, $kpis['sin']['total_registros']);
        $this->assertLessThan(4676, $kpis['sin']['total_registros']);
    }
}
