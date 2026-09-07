<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class MenuTest extends TestCase
{
    protected $token;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first();
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test de listar árbol de menús administrativos.
     */
    public function test_listar_menus()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/menu');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    /**
     * Test de navegación dinámica por usuario autenticado.
     */
    public function test_obtener_menu_navegacion_usuario()
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/usuario/menu-navegacion/'.$this->admin->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'menus' => [
                    '*' => [
                        'id',
                        'label',
                        'sub_menu',
                    ],
                ],
            ]);
    }

    /**
     * Test de creación, edición y eliminación de menú.
     */
    public function test_crud_menu()
    {
        // 1. Crear menú temporal
        $responseCreate = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/menu', [
                'label' => 'Módulo Test',
                'icon' => 'mdiTest',
                'level' => 0,
                'order' => 99,
            ]);

        $responseCreate->assertStatus(201);
        $menuId = $responseCreate->json('id');

        // 2. Actualizar menú
        $responseUpdate = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->putJson('/api/menu/'.$menuId, [
                'label' => 'Módulo Test Modificado',
                'icon' => 'mdiTestModified',
                'route' => 'test-route',
            ]);

        $responseUpdate->assertStatus(200);
        $this->assertEquals('Módulo Test Modificado', $responseUpdate->json('label'));

        // 3. Eliminar menú
        $responseDelete = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->deleteJson('/api/menu/'.$menuId);

        $responseDelete->assertStatus(200);
    }

    /**
     * Test de creación de permiso granular para un submenú.
     */
    public function test_crear_permiso_granular_submenu()
    {
        $submenu = Menu::whereNotNull('menu_id')->first();
        if (! $submenu) {
            $this->markTestSkipped('No hay submenús en la BD.');
        }

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->postJson('/api/menu/crear-permiso-submenu', [
                'menu_id' => $submenu->id,
                'nombre_accion' => 'exportar_excel',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }
}
