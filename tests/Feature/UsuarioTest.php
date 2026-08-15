<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RolUser;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class UsuarioTest extends TestCase
{
    protected $token;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('usr_usuario', 'admin')->first();
        if (!$this->admin) {
            $this->admin = User::create([
                'name' => 'Administrador Test',
                'usr_usuario' => 'admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('admin123456'),
                'usr_estado' => 'A',
            ]);
            $adminRole = Role::firstOrCreate(['name' => 'Administrador General', 'guard_name' => 'api']);
            $this->admin->assignRole($adminRole);
        }
        $this->token = JWTAuth::fromUser($this->admin);
    }

    /**
     * Test de listar usuarios del sistema.
     */
    public function test_listar_usuarios()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/usuario');

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
        $this->assertNotEmpty($response->json());
    }

    /**
     * Test de asignación y revocación de acceso a un usuario.
     */
    public function test_agregar_y_quitar_acceso_sistema()
    {
        $testUser = User::create([
            'name' => 'Usuario Prueba',
            'usr_usuario' => 'usuariotest_' . time(),
            'email' => 'test' . time() . '@test.com',
            'password' => Hash::make('secret123'),
            'usr_estado' => 'A',
        ]);

        // Asignar acceso al sistema
        $responseAdd = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/usuario/agregar-sistema/' . $testUser->id);

        $responseAdd->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'status' => 'success',
            ]);

        // Verificar que se haya registrado en RolUser
        $this->assertDatabaseHas('rol_users', [
            'usuario_id' => $testUser->id,
        ]);

        // Quitar acceso al sistema
        $responseRemove = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/usuario/quitar-sistema/' . $testUser->id);

        $responseRemove->assertStatus(200)
            ->assertJson([
                'code' => 200,
                'status' => 'success',
            ]);

        // Verificar que ya no tenga roles ni en rol_users activo
        $testUser->refresh();
        $this->assertCount(0, $testUser->roles);
    }

    /**
     * Test de actualización de contraseña segura.
     */
    public function test_actualizar_password_usuario()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/update_user_password', [
                'id' => $this->admin->id,
                'password' => 'nueva_password123',
                'current_password' => 'admin123456',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Restaurar contraseña original para mantener consistencia
        $this->admin->password = Hash::make('admin123456');
        $this->admin->save();
    }
}
