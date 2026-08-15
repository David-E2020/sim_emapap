<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class RolTest extends TestCase
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
     * Test de listar roles disponibles.
     */
    public function test_listar_roles()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/rol');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    /**
     * Test de creación, edición y eliminación de rol.
     */
    public function test_crud_rol()
    {
        // 1. Crear nuevo rol
        $rolNombre = 'Rol Temporal ' . time();
        $responseCreate = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/rol', [
                'name' => $rolNombre,
            ]);

        $responseCreate->assertStatus(201);
        $rolId = $responseCreate->json('id');

        // 2. Actualizar rol
        $nuevoNombre = $rolNombre . ' Editado';
        $responseUpdate = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/rol/' . $rolId, [
                'name' => $nuevoNombre,
            ]);

        $responseUpdate->assertStatus(200);
        $this->assertEquals($nuevoNombre, $responseUpdate->json('name'));

        // 3. Eliminar rol no asignado
        $responseDelete = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/rol/' . $rolId);

        $responseDelete->assertStatus(200);
    }

    /**
     * Test de asignación de menús a roles (toggle check).
     */
    public function test_asignar_menu_a_rol()
    {
        $menu = Menu::first();
        $rol = Rol::first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/menu-rol', [
                'menu_id' => $menu->id,
                'rol_id' => $rol->id,
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('menu_roles', [
            'menu_id' => $menu->id,
            'rol_id' => $rol->id,
        ]);
    }
}
