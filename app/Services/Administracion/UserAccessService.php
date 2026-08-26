<?php

declare(strict_types=1);

namespace App\Services\Administracion;

use App\Models\RolUser;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserAccessService
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Habilita el acceso del usuario al sistema asignando permiso base y rol.
     * Operación Transaccional ACID con Auditoría.
     */
    public function enableAccess(int $userId, ?int $roleId = null): User
    {
        return DB::transaction(function () use ($userId, $roleId) {
            $user = User::findOrFail($userId);

            // 1. Permiso base inmutable
            $permission = Permission::firstOrCreate([
                'name' => 'SIGP',
                'guard_name' => 'api'
            ]);
            $user->givePermissionTo($permission);

            // 2. Determinar Rol (especificado o predeterminado)
            $role = null;
            if ($roleId) {
                $role = Role::find($roleId);
            }
            if (!$role) {
                $role = Role::where('guard_name', 'api')->first() ?: Role::first();
            }

            if ($role) {
                $user->syncRoles([$role]);
                RolUser::updateOrCreate(
                    ['usuario_id' => $user->id],
                    [
                        'rol_id' => $role->id,
                        'estado' => true,
                        'usr_registrado' => auth()->id(),
                        'usr_modificado' => auth()->id(),
                    ]
                );
            }

            // 3. Registrar auditoría inmutable
            $this->auditService->log(
                event: 'user_access_enabled',
                model: $user,
                newValues: [
                    'role_id' => $role?->id,
                    'role_name' => $role?->name,
                    'permission' => 'SIGP'
                ]
            );

            return $user;
        }, attempts: 3);
    }

    /**
     * Revoca totalmente el acceso del usuario al sistema.
     * Operación Transaccional ACID con Auditoría.
     */
    public function revokeAccess(int $userId): User
    {
        return DB::transaction(function () use ($userId) {
            $user = User::findOrFail($userId);

            $oldRoles = $user->getRoleNames()->toArray();
            $oldPermissions = $user->getAllPermissions()->pluck('name')->toArray();

            // Revocar roles y permisos Spatie
            $user->syncPermissions([]);
            $user->syncRoles([]);

            // Eliminar relación en tabla pivote interna
            RolUser::where('usuario_id', $userId)->delete();

            // Registrar auditoría inmutable
            $this->auditService->log(
                event: 'user_access_revoked',
                model: $user,
                oldValues: [
                    'roles' => $oldRoles,
                    'permissions' => $oldPermissions
                ]
            );

            return $user;
        }, attempts: 3);
    }

    /**
     * Asigna o actualiza el rol de un usuario de forma atómica.
     * Operación Transaccional ACID con Auditoría.
     */
    public function assignRole(int $userId, int $roleId): RolUser
    {
        return DB::transaction(function () use ($userId, $roleId) {
            $user = User::findOrFail($userId);
            $role = Role::findOrFail($roleId);

            $oldRolUser = RolUser::where('usuario_id', $userId)->first();
            $oldRoleId = $oldRolUser?->rol_id;

            // 1. Asignar/Sincronizar en Spatie
            $permission = Permission::firstOrCreate(['name' => 'SIGP', 'guard_name' => 'api']);
            $user->givePermissionTo($permission);
            $user->syncRoles([$role]);

            // 2. Asignar en tabla pivote de roles interna
            $rolUser = RolUser::updateOrCreate(
                ['usuario_id' => $userId],
                [
                    'rol_id' => $roleId,
                    'estado' => true,
                    'usr_modificado' => auth()->id(),
                ]
            );

            // 3. Auditoría inmutable
            $this->auditService->log(
                event: 'user_role_updated',
                model: $user,
                oldValues: ['rol_id' => $oldRoleId],
                newValues: ['rol_id' => $roleId, 'role_name' => $role->name]
            );

            return $rolUser;
        }, attempts: 3);
    }
}
