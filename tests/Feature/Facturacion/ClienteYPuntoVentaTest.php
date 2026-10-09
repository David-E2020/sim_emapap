<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\Facturacion\ClienteFactura;
use App\Models\Facturacion\ProductoServicioFactura;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class ClienteYPuntoVentaTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::first() ?? User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    public function test_gestion_padron_clientes_crear_y_listar(): void
    {
        // 1. Crear nuevo cliente
        $resCrear = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/clientes', [
                'codigo_tipo_documento_identidad' => 1,
                'numero_documento' => '7891234',
                'nombre_razon_social' => 'FLORES CONDORI CARLOS',
                'correo_electronico' => 'carlos.flores@patacamaya.bo',
                'telefono' => '71512345',
            ]);

        $resCrear->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('facturacion.clientes', [
            'numero_documento' => '7891234',
            'nombre_razon_social' => 'FLORES CONDORI CARLOS',
        ]);

        // 2. Listar clientes con búsqueda
        $resListar = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/clientes?search=7891234');

        $resListar->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.0.numero_documento', '7891234');
    }

    public function test_creacion_de_servicio_en_catalogo_emapap(): void
    {
        $res = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/siat/productos', [
                'codigo_producto_empresa' => 'AGUA-IND-01',
                'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA INDUSTRIAL',
                'precio_unitario' => 65.00,
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'codigo_unidad_medida' => 58,
            ]);

        $res->assertStatus(201)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('facturacion.productos_servicios', [
            'codigo_producto_empresa' => 'AGUA-IND-01',
        ]);
    }

    public function test_registro_cuis_y_cierre_de_punto_de_venta(): void
    {
        $sucursal = SiatSucursal::first() ?? SiatSucursal::create([
            'codigo_sucursal' => 0,
            'nombre' => 'Matriz Patacamaya',
            'direccion' => 'Plaza 15 de Agosto',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'TEST',
            '_usuario_creacion' => 1,
        ]);

        // 1. Registrar punto de venta ante el SIN
        $resRegistro = $this->withHeaders($this->authHeaders())
            ->postJson('/api/facturacion/siat/puntos-venta', [
                'id_sucursal' => $sucursal->id,
                'nombre' => 'Caja Recaudación Terminal',
                'descripcion' => 'Ventanilla en Terminal de Buses Patacamaya',
                'tipo_punto_venta' => 2, // 2: Ventanilla de Cobranza (homologado SIN)
            ]);

        $resRegistro->assertStatus(201)
            ->assertJson(['success' => true]);

        $puntoVentaId = $resRegistro->json('data.id');
        $this->assertNotNull($puntoVentaId);

        // 2. Renovar CUIS para ese punto de venta
        $resCuis = $this->withHeaders($this->authHeaders())
            ->postJson("/api/facturacion/siat/puntos-venta/{$puntoVentaId}/cuis");

        $resCuis->assertStatus(200)
            ->assertJson(['success' => true]);

        // 3. Cerrar punto de venta ante el SIN
        $resCierre = $this->withHeaders($this->authHeaders())
            ->postJson("/api/facturacion/siat/puntos-venta/{$puntoVentaId}/cierre");

        $resCierre->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('facturacion.puntos_venta', [
            'id' => $puntoVentaId,
            '_estado' => 'CERRADO',
        ]);
    }
}
