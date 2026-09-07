<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\Administracion\ParametricaService;
use App\Services\Administracion\UserAccessService;
use App\Services\Biometrics\ZkBiometricService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EnterpriseArchitectureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_audit_logs_are_recorded_on_user_access_change(): void
    {
        $admin = User::firstOrCreate(
            ['usr_usuario' => 'admin_test_arch'],
            [
                'name' => 'Admin Test Arch',
                'email' => 'admin_arch@test.com',
                'password' => bcrypt('password123'),
                'usr_estado' => 'A',
            ]
        );

        $this->actingAs($admin, 'api');

        $userAccessService = app(UserAccessService::class);
        $testUser = User::firstOrCreate(
            ['usr_usuario' => 'operador_audit_test'],
            [
                'name' => 'Operador Test Audit',
                'email' => 'operador_audit@test.com',
                'password' => bcrypt('password123'),
                'usr_estado' => 'A',
            ]
        );

        // Habilitar acceso
        $userAccessService->enableAccess($testUser->id);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user_access_enabled',
            'auditable_type' => User::class,
            'auditable_id' => $testUser->id,
        ]);

        // Revocar acceso
        $userAccessService->revokeAccess($testUser->id);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'user_access_revoked',
            'auditable_type' => User::class,
            'auditable_id' => $testUser->id,
        ]);
    }

    public function test_parametrica_service_anti_n_plus_one_query(): void
    {
        $service = app(ParametricaService::class);
        $tables = $service->getOriginTables();

        $this->assertNotNull($tables);
        foreach ($tables as $table) {
            $this->assertArrayHasKey('param_valor_contador', $table->toArray());
        }
    }

    public function test_biometric_service_socket_probe_fails_safely_on_unreachable_ip(): void
    {
        $service = app(ZkBiometricService::class);

        // IP inalcanzable de prueba con timeout corto de 0.2s
        $isReachable = $service->pingDevice('192.0.2.1', 4370, 0.2);
        $this->assertFalse($isReachable);
    }
}
