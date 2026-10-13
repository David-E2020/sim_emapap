<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\User;
use App\Services\Facturacion\CufService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tests\CreatesApplication;

class CufHoraYDuplicadosTest extends BaseTestCase
{
    use CreatesApplication;

    protected string $token;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::where('email', 'admin@emapa.gob.bo')->first() ?: User::first();
        if ($this->user) {
            $rolAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador General', 'guard_name' => 'api']);
            if (!$this->user->hasRole('Administrador General')) {
                $this->user->assignRole($rolAdmin);
            }
            $this->token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($this->user);
        }
    }

    /**
     * Prueba que el decodificador matemático de CUF extraiga exactamente la fecha y hora oficial.
     */
    public function test_decodificacion_cuf_extrae_fecha_hora_exacta_y_campos_oficiales(): void
    {
        $cufService = app(CufService::class);
        $cuf = '449625CCC6D3AE2AE3D22E3B19483F36C5C542E6A718DE5574653BF74';

        $decoded = $cufService->decodificarCuf($cuf);

        $this->assertNotNull($decoded);
        $this->assertEquals('1002393029', $decoded['nit']);
        $this->assertEquals(47873, $decoded['numero_factura']);
        $this->assertEquals(13, $decoded['documento_sector']);
        $this->assertEquals(0, $decoded['sucursal']);
        $this->assertEquals(0, $decoded['punto_venta']);
        $this->assertEquals(1, $decoded['modalidad']);
        $this->assertEquals(7, $decoded['modulo11']);

        $fechaExtraida = $cufService->extraerFechaHoraDesdeCuf($cuf);
        $this->assertInstanceOf(Carbon::class, $fechaExtraida);
        $this->assertEquals('2026-09-30 16:56:52', $fechaExtraida->format('Y-m-d H:i:s'));
    }

    /**
     * Prueba que el comando artisan facturacion:sincronizar-horas-cuf corra limpiamente.
     */
    public function test_comando_sincronizar_horas_cuf_en_dry_run(): void
    {
        $this->artisan('facturacion:sincronizar-horas-cuf', ['--dry-run' => true])
            ->assertSuccessful();
    }

    /**
     * Prueba que el endpoint de facturas incluya la auditoría preventiva de CUFs duplicados.
     */
    public function test_api_facturas_retorna_auditoria_cufs_duplicados(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/facturacion/facturas');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'total',
            'auditoria_cuf' => [
                'total_cufs_duplicados',
                'integro',
            ],
        ]);

        $this->assertIsInt($response->json('auditoria_cuf.total_cufs_duplicados'));
        $this->assertIsBool($response->json('auditoria_cuf.integro'));
    }

    /**
     * Prueba que el filtro de facturas duplicadas funcione y responda 200.
     */
    public function test_filtro_facturas_duplicadas_responde_200(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/facturacion/facturas?estado=DUPLICADAS');

        $response->assertStatus(200);
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Prueba que el endpoint de Libro de Ventas retorne auditoría de duplicados y marque filas duplicadas.
     */
    public function test_api_libro_ventas_retorna_auditoria_duplicados_y_filas_marcadas(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/facturacion/reportes/libro-ventas?gestion=2026&periodo=9');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'resumen' => [
                'cantidad_duplicadas',
                'total_numeros_duplicados',
                'ejemplos_duplicados',
            ],
            'auditoria_duplicados' => [
                'cantidad_duplicadas',
                'total_numeros_duplicados',
                'ejemplos_duplicados',
            ],
        ]);

        $this->assertIsInt($response->json('auditoria_duplicados.cantidad_duplicadas'));
    }

    /**
     * Prueba que el filtro estado=DUPLICADAS en Libro de Ventas devuelva solo registros duplicados.
     */
    public function test_filtro_duplicadas_en_libro_ventas(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/facturacion/reportes/libro-ventas?gestion=2026&periodo=9&estado=DUPLICADAS');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);

        foreach ($data as $item) {
            $this->assertTrue($item['es_duplicada'], 'Todos los elementos devueltos con filtro DUPLICADAS deben tener es_duplicada = true');
        }
    }

    /**
     * Prueba que las 6 modalidades de exportación con alertas de duplicados generen HTTP 200.
     */
    public function test_exportaciones_con_alertas_duplicados_responden_200(): void
    {
        // 1. Libro de Ventas RCV Excel
        $respRcvExcel = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->get('/api/facturacion/reportes/libro-ventas/exportar-excel?gestion=2026&periodo=9');
        $respRcvExcel->assertStatus(200);

        // 2. Libro de Ventas RCV CSV
        $respRcvCsv = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->get('/api/facturacion/reportes/libro-ventas/exportar-csv?gestion=2026&periodo=9');
        $respRcvCsv->assertStatus(200);

        // 3. Libro de Ventas RCV PDF
        $respRcvPdf = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->get('/api/facturacion/reportes/libro-ventas/exportar-pdf?gestion=2026&periodo=9');
        $respRcvPdf->assertStatus(200);

        // 4. Bandeja Facturas Excel
        $respFacExcel = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->get('/api/facturacion/facturas/exportar-excel?gestion=2026&formato=excel');
        $respFacExcel->assertStatus(200);

        // 5. Bandeja Facturas CSV
        $respFacCsv = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->get('/api/facturacion/facturas/exportar-excel?gestion=2026&formato=csv');
        $respFacCsv->assertStatus(200);

        // 6. Bandeja Facturas PDF
        $respFacPdf = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->get('/api/facturacion/facturas/exportar-pdf?gestion=2026');
        $respFacPdf->assertStatus(200);
    }

    /**
     * Prueba que las facturas actualizadas en base de datos ya no tengan hora 00:00:00 para SIAT.
     */
    public function test_facturas_siat_tienen_hora_real_en_base_de_datos(): void
    {
        $facturaPrueba = DB::table('facturacion.facturas')
            ->where('cuf', '449625CCC6D3AE2AE3D22E3B19483F36C5C542E6A718DE5574653BF74')
            ->first();

        if ($facturaPrueba) {
            $this->assertEquals('2026-09-30 16:56:52', Carbon::parse($facturaPrueba->fecha_emision)->format('Y-m-d H:i:s'));
        } else {
            $this->markTestSkipped('Factura 47873 de prueba no encontrada en esta base de datos.');
        }
    }
}

