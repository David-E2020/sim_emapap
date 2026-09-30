<?php

declare(strict_types=1);

namespace Tests\Feature\Datos;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MigracionFoxProTest extends TestCase
{
    protected string $token;
    protected User $admin;
    protected string $rutaRespaldoReal = '/home/david/Documentos/Mis Proyectos/Sistemas Emapa 2025/SRV EMAPA COMPARTIDO/DATA_19_09_2026/DATA';

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first() ?? User::first();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test de consulta de rutas predefinidas de respaldos en el servidor.
     */
    public function test_obtener_rutas_predefinidas(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/datos/migracion/rutas-predefinidas');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $rutas = $response->json('rutas');
        $this->assertIsArray($rutas);
        $this->assertNotEmpty($rutas);

        // Al menos una ruta debe existir en el entorno de desarrollo
        $existeAlguna = collect($rutas)->contains('existe', true);
        $this->assertTrue($existeAlguna, 'Debe haber al menos un respaldo detectado en el servidor.');
    }

    /**
     * Test de explorador de directorios en el servidor.
     */
    public function test_explorar_servidor_directorios(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/explorar-servidor', [
                'ruta' => $this->rutaRespaldoReal,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertTrue($response->json('es_directorio_dbf'), 'La carpeta DATA debe ser detectada como directorio DBF.');
        $this->assertGreaterThan(10, $response->json('total_dbfs'));
        $this->assertNotEmpty($response->json('archivos_dbf_muestra'));
    }

    /**
     * Test de escaneo y comparativa volumétrica multiesquema FoxPro vs PostgreSQL.
     */
    public function test_escanear_directorio_real_foxpro(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/escanear', [
                'ruta' => $this->rutaRespaldoReal,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $totalDbf = $response->json('total_registros_dbf');
        $tablas = $response->json('tablas');
        $resumen = $response->json('resumen_modulos');

        // Validar que se detectaron registros en el respaldo (> 500.000)
        $this->assertGreaterThan(500000, $totalDbf);

        // Validar que los 5 esquemas principales están cubiertos
        $this->assertArrayHasKey('comercial', $resumen);
        $this->assertArrayHasKey('facturacion', $resumen);
        $this->assertArrayHasKey('contabilidad', $resumen);
        $this->assertArrayHasKey('almacen', $resumen);
        $this->assertArrayHasKey('activos_fijos', $resumen);

        // Validar tablas puntuales
        $tablaAbonados = collect($tablas)->firstWhere('id', 'abonados');
        $this->assertNotNull($tablaAbonados);
        $this->assertEquals('comercial', $tablaAbonados['schema']);
        $this->assertGreaterThan(5000, $tablaAbonados['dbf_registros']);

        $tablaFacturas = collect($tablas)->firstWhere('id', 'facturas');
        $this->assertNotNull($tablaFacturas);
        $this->assertEquals('facturacion', $tablaFacturas['schema']);
        $this->assertGreaterThan(200000, $tablaFacturas['dbf_registros']);
    }

    /**
     * Test de ejecución en modo simulación (Dry-Run) con límite de registros.
     */
    public function test_ejecutar_simulacion_dry_run_calles_y_aportes(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/ejecutar', [
                'ruta' => $this->rutaRespaldoReal,
                'modulos' => ['calles', 'aportes_agua'],
                'es_simulacion' => true,
                'limite' => 50,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'es_simulacion' => true,
            ]);

        $jobId = $response->json('job_id');
        $this->assertNotEmpty($jobId);

        $logs = $response->json('logs');
        $this->assertIsArray($logs);
        $this->assertNotEmpty($logs);

        // Validar que se grabó la bitácora en migracion.logs
        $logsBd = DB::table('migracion.logs')->where('job_id', $jobId)->get();
        $this->assertNotEmpty($logsBd);
        $this->assertTrue((bool) $logsBd->first()->es_simulacion);
    }

    /**
     * Test de ejecución en modo simulación de TODOS los módulos juntos.
     */
    public function test_ejecutar_simulacion_dry_run_todos_los_modulos(): void
    {
        $todos = [
            'calles', 'zonas', 'tarifas', 'abonados', 'aportes_agua',
            'aportes_alcantarillado', 'bajas_socios', 'convenios', 'recibos',
            'lecturas', 'facturas', 'plan_cuentas', 'comprobantes', 'compras',
            'materiales_almacen', 'rubros_activos', 'bienes_activos'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/ejecutar', [
                'ruta' => $this->rutaRespaldoReal,
                'modulos' => $todos,
                'es_simulacion' => true,
                'limite' => 20,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'es_simulacion' => true,
            ]);

        $logs = $response->json('logs');
        $this->assertCount(count($todos), $logs);

        // Ningún módulo debe haber fallado con ERROR
        $errores = collect($logs)->where('estado', 'ERROR')->pluck('mensaje')->toArray();
        $this->assertEmpty($errores, 'No debe haber errores en la simulación: ' . implode(', ', $errores));
    }

    /**
     * Test de validación de esquemas y tablas creadas en PostgreSQL.
     */
    public function test_verificar_esquemas_y_tablas_en_postgresql(): void
    {
        $schemas = collect(DB::select("
            SELECT table_schema 
            FROM information_schema.tables 
            WHERE table_schema IN ('comercial', 'facturacion', 'contabilidad', 'almacen', 'activos_fijos', 'migracion')
            GROUP BY table_schema
        "))->pluck('table_schema')->toArray();

        $this->assertContains('comercial', $schemas);
        $this->assertContains('facturacion', $schemas);
        $this->assertContains('contabilidad', $schemas);
        $this->assertContains('almacen', $schemas);
        $this->assertContains('activos_fijos', $schemas);
        $this->assertContains('migracion', $schemas);

        // Verificar tablas nuevas complementarias
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('comercial.aportes_conexiones'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('comercial.abonados_bajas'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('contabilidad.facturas_compra'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('almacen.materiales'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('activos_fijos.rubros'));
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('migracion.logs'));
    }

    /**
     * Test de consulta del historial de migraciones (Bitácora).
     */
    public function test_consultar_historial_migraciones(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/datos/migracion/historial');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $logs = $response->json('logs');
        $kpis = $response->json('kpis');

        $this->assertIsArray($logs);
        $this->assertIsArray($kpis);
        $this->assertArrayHasKey('total_migraciones', $kpis);
        $this->assertArrayHasKey('total_procesados', $kpis);
    }

    /**
     * Test del motor de reversión transaccional (Rollback).
     */
    public function test_revertir_migracion_rollback(): void
    {
        // Ejecutar una simulación para generar un Job ID
        $exec = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/ejecutar', [
                'ruta' => $this->rutaRespaldoReal,
                'modulos' => ['calles'],
                'es_simulacion' => true,
                'limite' => 5,
            ]);

        $jobId = $exec->json('job_id');
        $this->assertNotEmpty($jobId);

        // Revertir el Job
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/revertir', [
                'job_id' => $jobId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $resultado = $response->json('resultado');
        $this->assertEquals($jobId, $resultado['job_id']);
        $this->assertArrayHasKey('registros_eliminados', $resultado);
    }

    /**
     * Test de inicio en segundo plano y consulta de estado (ligero y asíncrono).
     */
    public function test_iniciar_fondo_y_consultar_estado(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/iniciar-fondo', [
                'ruta' => $this->rutaRespaldoReal,
                'es_simulacion' => true,
                'limite' => 5,
                'modulos' => ['calles'],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $jobId = $response->json('job_id');
        $this->assertNotEmpty($jobId);

        // Consultar estado del job
        $resEstado = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/datos/migracion/estado-job/{$jobId}");

        $resEstado->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ]);

        $this->assertEquals($jobId, $resEstado->json('data.job_id'));
    }

    /**
     * Test de cancelación inmediata de job en segundo plano.
     */
    public function test_cancelar_job(): void
    {
        // Iniciar un job
        $init = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/datos/migracion/iniciar-fondo', [
                'ruta' => $this->rutaRespaldoReal,
                'es_simulacion' => true,
                'limite' => 5,
                'modulos' => ['calles'],
            ]);

        $jobId = $init->json('job_id');
        $this->assertNotEmpty($jobId);

        // Cancelar inmediatamente
        $resCancel = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson("/api/datos/migracion/cancelar-job/{$jobId}");

        $resCancel->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Proceso cancelado exitosamente.',
            ]);

        // Verificar estado CANCELADO
        $resEstado = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("/api/datos/migracion/estado-job/{$jobId}");

        $resEstado->assertStatus(200);
        $this->assertEquals('CANCELADO', $resEstado->json('data.estado'));
    }

    /**
     * Test de subida por fragmentos (Chunks) para respaldos de gran volumen (195MB+).
     */
    public function test_subir_chunk_fragmentos_ensamblaje(): void
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'test_zip_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE);
        $zip->addFromString('DATA/test_calles.dbf', 'CONTENIDO_DBF_FICTICIO');
        $zip->close();

        $zipData = file_get_contents($zipPath);
        $totalLength = strlen($zipData);
        $chunkSize = (int) ceil($totalLength / 2);
        $part0 = substr($zipData, 0, $chunkSize);
        $part1 = substr($zipData, $chunkSize);

        $fileId = 'test_upload_' . uniqid();
        $uploadedPart0 = \Illuminate\Http\UploadedFile::fake()->createWithContent('backup.zip', $part0);

        // Enviar chunk 0
        $resChunk0 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/datos/migracion/subir-chunk', [
                'chunk' => $uploadedPart0,
                'chunk_index' => 0,
                'total_chunks' => 2,
                'file_id' => $fileId,
                'file_name' => 'backup.zip',
            ]);

        $resChunk0->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'completado' => false,
            ]);

        // Enviar chunk 1 (final)
        $uploadedPart1 = \Illuminate\Http\UploadedFile::fake()->createWithContent('backup.zip', $part1);
        $resChunk1 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->post('/api/datos/migracion/subir-chunk', [
                'chunk' => $uploadedPart1,
                'chunk_index' => 1,
                'total_chunks' => 2,
                'file_id' => $fileId,
                'file_name' => 'backup.zip',
            ]);

        $resChunk1->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'completado' => true,
            ]);

        $this->assertNotEmpty($resChunk1->json('ruta_extraida'));
        $this->assertFileExists($resChunk1->json('ruta_extraida') . '/test_calles.dbf');

        // Limpieza
        @unlink($zipPath);
    }
}
