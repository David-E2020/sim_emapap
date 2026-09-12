<?php

declare(strict_types=1);

namespace Tests\Feature\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CajaSesion;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\Zona;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CajaInteractiveWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected User $cajero;
    protected string $token;
    protected SiatSucursal $sucursal;
    protected SiatPuntoVenta $puntoVenta;
    protected Abonado $abonado;
    protected LecturaMensual $lectura;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cajero = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->cajero);

        // Asegurar sucursal y punto de venta
        $this->sucursal = SiatSucursal::firstOrCreate(
            ['codigo_sucursal' => 0],
            [
                'nombre' => 'EMAPAP Central Patacamaya',
                'direccion' => 'Av. Panamericana s/n',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
            ]
        );

        $this->puntoVenta = SiatPuntoVenta::where('_estado', 'ACTIVO')->first()
            ?? SiatPuntoVenta::create([
                'id_sucursal' => $this->sucursal->id,
                'codigo_punto_venta' => 0,
                'nombre' => 'Caja Central Principal',
                'tipo_punto_venta' => 0,
                'descripcion' => 'Ventanilla Central de Cobranzas',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
                '_usuario_creacion' => $this->cajero->id,
            ]);

        // Asegurar abonado con lectura pendiente
        $zona = Zona::first() ?? Zona::create([
            'codigo' => 'Z1',
            'nombre' => 'Zona Central',
            'activo' => true,
        ]);

        $cat = CategoriaTarifaria::where('activo', true)->first() ?? CategoriaTarifaria::first() ?? CategoriaTarifaria::create([
            'codigo' => 'D',
            'nombre' => 'DOMICILIARIA',
            'volumen_base' => 6.00,
            'tarifa_minima' => 12.60,
            'tarifa_excedente_base' => 2.10,
            'tarifa_alcantarillado' => 2.00,
            'aplica_ley_1886' => true,
            'activo' => true,
        ]);

        $this->abonado = Abonado::firstOrCreate(
            ['codigo' => 'TEST-00999'],
            [
                'nombre_completo' => 'USUARIO PRUEBA INTERACTIVA',
                'numero_documento' => '12345678',
                'codigo_tipo_documento_identidad' => 1,
                'complemento' => null,
                'id_categoria' => $cat->id,
                'id_zona' => $zona->id,
                'estado_servicio' => 'ACTIVO',
                'direccion' => 'Calle Principal #100',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
                '_usuario_creacion' => $this->cajero->id,
            ]
        );

        $periodo = PeriodoFacturacion::firstOrCreate(
            ['gestion' => 2026, 'mes' => 8],
            [
                'codigo_periodo' => '2026-08',
                'fecha_inicio' => '2026-08-01',
                'fecha_fin' => '2026-08-31',
                'fecha_vencimiento' => '2026-09-15',
                'estado' => 'ABIERTO',
                'activo' => true,
            ]
        );

        $this->lectura = LecturaMensual::firstOrCreate(
            [
                'id_abonado' => $this->abonado->id,
                'id_periodo' => $periodo->id,
            ],
            [
                'lectura_anterior' => 10.00,
                'lectura_actual' => 16.00,
                'consumo_m3' => 6.00,
                'monto_agua' => 12.60,
                'monto_alcantarillado' => 2.00,
                'monto_descuento_ley1886' => 0.00,
                'monto_otros' => 0.00,
                'total_facturado' => 14.60,
                'estado_pago' => 'PENDIENTE',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'TEST',
                '_usuario_creacion' => $this->cajero->id,
            ]
        );
    }

    protected function headers(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_flujo_completo_interactivo_caja_cobranza(): void
    {
        // PASO 1: Consultar cajas disponibles para apertura
        $resDisponibles = $this->withHeaders($this->headers())
            ->getJson('/api/comercial/caja-sesiones/cajas-disponibles');

        $resDisponibles->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertNotEmpty($resDisponibles->json('data'));

        // PASO 2: Apertura formal de turno de caja
        $montoApertura = 100.00;
        $resApertura = $this->withHeaders($this->headers())
            ->postJson('/api/comercial/caja-sesiones/abrir', [
                'id_punto_venta' => $this->puntoVenta->id,
                'monto_apertura' => $montoApertura,
                'observaciones_apertura' => 'Apertura de prueba interactiva automatizada',
            ]);

        $resApertura->assertStatus(201)
            ->assertJson(['success' => true]);

        $sesionData = $resApertura->json('data');
        $this->assertNotNull($sesionData);
        $sesionId = $sesionData['id'];
        $this->assertEquals('ABIERTA', $sesionData['estado']);
        $this->assertEquals($montoApertura, (float) $sesionData['monto_apertura']);

        // PASO 3: Intentar apertura concurrente duplicada (debe fallar con 409 Conflict)
        $resDuplicada = $this->withHeaders($this->headers())
            ->postJson('/api/comercial/caja-sesiones/abrir', [
                'id_punto_venta' => $this->puntoVenta->id,
                'monto_apertura' => 50.00,
            ]);

        $resDuplicada->assertStatus(409)
            ->assertJson(['success' => false]);

        // PASO 4: Consultar estado de cuenta del abonado
        $resCuenta = $this->withHeaders($this->headers())
            ->getJson('/api/comercial/caja/estado-cuenta/' . $this->abonado->codigo);

        $resCuenta->assertStatus(200)
            ->assertJson(['success' => true]);

        $estadoCuenta = $resCuenta->json('data');
        $this->assertEquals($this->abonado->codigo, $estadoCuenta['abonado']['codigo']);
        $this->assertNotEmpty($estadoCuenta['lecturas_pendientes']);

        // PASO 5: Cobro en ventanilla y emisión de comprobante
        $resCobro = $this->withHeaders($this->headers())
            ->postJson('/api/comercial/caja/cobrar', [
                'id_abonado' => $this->abonado->id,
                'lecturas_ids' => [$this->lectura->id],
                'cuotas_ids' => [],
                'codigo_metodo_pago' => 1, // 1 = Efectivo
                'nombre_razon_social' => $this->abonado->nombre_completo,
                'numero_documento' => $this->abonado->numero_documento,
                'codigo_tipo_documento_identidad' => 1,
                'id_sucursal' => $this->sucursal->id,
                'id_punto_venta' => $this->puntoVenta->id,
            ]);

        $resCobro->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verificar que la lectura cambió a PAGADO en base de datos
        $this->lectura->refresh();
        $this->assertEquals('PAGADO', $this->lectura->estado_pago);
        $this->assertNotNull($this->lectura->fecha_pago);

        // PASO 6: Consultar resumen de arqueo de la sesión activa
        $resArqueo = $this->withHeaders($this->headers())
            ->getJson('/api/comercial/caja-sesiones/resumen-arqueo');

        $resArqueo->assertStatus(200)
            ->assertJson(['success' => true]);

        $arqueoData = $resArqueo->json('data');
        $this->assertNotNull($arqueoData);
        $this->assertNotNull($arqueoData['sesion']);
        $this->assertGreaterThan(0, (float) $arqueoData['sesion']['monto_ventas_efectivo']);

        // PASO 7: Cierre y arqueo formal de la caja
        $montoEsperado = (float) $arqueoData['sesion']['monto_esperado_efectivo'];
        $resCierre = $this->withHeaders($this->headers())
            ->postJson('/api/comercial/caja-sesiones/cerrar', [
                'id_sesion' => $sesionId,
                'monto_cierre_declarado' => $montoEsperado,
                'observaciones_cierre' => 'Cierre exitoso verificado en test interactivo',
            ]);

        $resCierre->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verificar que la sesión quedó CERRADA
        $sesionDb = CajaSesion::find($sesionId);
        $this->assertEquals('CERRADA', $sesionDb->estado);
        $this->assertNotNull($sesionDb->fecha_cierre);
    }
}
