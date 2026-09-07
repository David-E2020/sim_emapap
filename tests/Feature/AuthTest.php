<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    /**
     * Test de inicio de sesión exitoso con credenciales correctas.
     */
    public function test_login_con_credenciales_validas()
    {
        $response = $this->postJson('/api/login', [
            'usr_usuario' => 'admin',
            'password' => 'admin123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'token',
                'user',
                'permissions',
                'roles',
                'rol',
                'rute_home',
            ]);

        $this->assertEquals('success', $response->json('status'));
        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test de inicio de sesión fallido con contraseña incorrecta.
     */
    public function test_login_con_credenciales_invalidas()
    {
        $response = $this->postJson('/api/login', [
            'usr_usuario' => 'admin',
            'password' => 'password_erronea',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Las credenciales son incorrectas.',
            ]);
    }

    /**
     * Test de cierre de sesión exitoso (JWT invalidation).
     */
    public function test_logout_exitoso()
    {
        $user = User::first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente.',
            ]);
    }
}
