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
    public function test_login_con_credenciales_validas(): void
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
    public function test_login_con_credenciales_invalidas(): void
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
     * Test de inicio de sesión de usuario recién creado sin roles asignados.
     * Debe retornar 403 con código ACCOUNT_NO_ROLES y mensaje estructurado e informativo.
     */
    public function test_login_bloqueado_para_usuario_sin_roles(): void
    {
        $usuario = User::create([
            'name' => 'Personal Nuevo Test',
            'email' => 'personal.nuevo@emapa.com',
            'usr_usuario' => 'test_sin_roles',
            'password' => bcrypt('password123'),
            'usr_estado' => 'A',
        ]);

        try {
            $response = $this->postJson('/api/login', [
                'usr_usuario' => 'test_sin_roles',
                'password' => 'password123',
            ]);

            $response->assertStatus(403)
                ->assertJson([
                    'status' => 'error',
                    'code' => 'ACCOUNT_NO_ROLES',
                    'title' => 'Cuenta pendiente de asignación de rol',
                    'user' => [
                        'nombre' => 'Personal Nuevo Test',
                        'usuario' => 'test_sin_roles',
                    ],
                ]);

            $this->assertStringContainsString('rol operativo asignado', $response->json('message'));
        } finally {
            $usuario->forceDelete();
        }
    }

    /**
     * Test de inicio de sesión de usuario institucional inactivo/suspendido.
     * Debe retornar 403 con código ACCOUNT_SUSPENDED.
     */
    public function test_login_bloqueado_para_usuario_inactivo(): void
    {
        $usuario = User::create([
            'name' => 'Personal Suspendido Test',
            'email' => 'personal.suspendido@emapa.com',
            'usr_usuario' => 'test_suspendido',
            'password' => bcrypt('password123'),
            'usr_estado' => 'I',
        ]);

        try {
            $response = $this->postJson('/api/login', [
                'usr_usuario' => 'test_suspendido',
                'password' => 'password123',
            ]);

            $response->assertStatus(403)
                ->assertJson([
                    'status' => 'error',
                    'code' => 'ACCOUNT_SUSPENDED',
                    'title' => 'Cuenta institucional inactiva',
                ]);
        } finally {
            $usuario->forceDelete();
        }
    }

    /**
     * Test de cierre de sesión exitoso (JWT invalidation).
     */
    public function test_logout_exitoso(): void
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

    /**
     * Test de cierre de sesión con token inválido/expirado (idempotencia sin error 500).
     */
    public function test_logout_idempotente_con_token_invalido(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer token_invalido_o_expirado')
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente.',
            ]);
    }

    /**
     * Test de cierre de sesión sin cabecera Authorization (no debe arrojar 500).
     */
    public function test_logout_sin_token_retorna_200(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Sesión cerrada correctamente.',
            ]);
    }

    /**
     * Test de vista web /login: Retorna 200, splash screen y carga de app.js.
     */
    public function test_vista_login_retorna_200_y_splash_screen(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('app-splash-loader', false);
        $response->assertSee('EMAPAP', false);
        $response->assertSee('Iniciando sistema...', false);
        $response->assertSee('js/app.js', false);
    }

    /**
     * Test de vista web /pages/login: Retorna 200 para que Vue Router ejecute el redirect.
     */
    public function test_vista_pages_login_retorna_200(): void
    {
        $response = $this->get('/pages/login');
        $response->assertStatus(200);
    }

    /**
     * Test de integridad de assets estáticos del login.
     */
    public function test_assets_login_optimizados_y_presentes(): void
    {
        $this->assertFileExists(public_path('js/app.js'));
        $this->assertFileExists(public_path('images/logo-ciclo.mp4'));
        $this->assertFileExists(public_path('images/logo-ciclo-poster.jpg'));

        // El video optimizado debe pesar menos de 500 KB (era de 2.5 MB)
        $videoSize = filesize(public_path('images/logo-ciclo.mp4'));
        $this->assertLessThan(500 * 1024, $videoSize, 'El video de fondo supera los 500 KB');

        // El póster optimizado debe pesar menos de 100 KB
        $posterSize = filesize(public_path('images/logo-ciclo-poster.jpg'));
        $this->assertLessThan(100 * 1024, $posterSize, 'El póster supera los 100 KB');
    }
}
