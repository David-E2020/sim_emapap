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
