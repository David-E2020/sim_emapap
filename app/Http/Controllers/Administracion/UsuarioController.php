<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    /**
     * Listado de usuarios activos con sus roles y permisos.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::with('roles', 'permissions', 'rolPersmisos.rol')
            ->where('usr_estado', 'A')
            ->orderBy('id', 'asc')
            ->get()
            ->makeHidden(['deleted_at', 'usr_archivo', 'usr_modificado', 'usr_registrado']);

        return response()->json($users, 200);
    }

    /**
     * Deshabilitar/quitar acceso de un usuario al sistema.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function quitarSistema($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                $user->syncPermissions([]);
                $user->syncRoles([]);
                RolUser::where('usuario_id', $id)->delete();
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Se quitó el acceso al sistema',
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'mensaje' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Habilitar/asignar acceso de un usuario al sistema con rol predeterminado.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function agregarSistema($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                // Permiso de acceso base
                $permission = Permission::firstOrCreate(['name' => 'SIGP', 'guard_name' => 'api']);
                $user->givePermissionTo($permission);

                // Asignar rol predeterminado
                $role = Role::where('guard_name', 'api')->first() ?: Role::first();
                if ($role) {
                    $user->assignRole($role);
                    RolUser::updateOrCreate(
                        ['usuario_id' => $user->id],
                        ['rol_id' => $role->id, 'estado' => true]
                    );
                }
            }

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Se asignó el acceso al sistema correctamente',
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 'error',
                'mensaje' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener roles disponibles y rol actual de un usuario.
     *
     * @param int $userId
     * @return array
     */
    public function rolUser($userId)
    {
        $rolUser_ = RolUser::where('usuario_id', $userId)->first();
        $usuarioRolId = $rolUser_ ? $rolUser_->rol_id : null;
        $roles_ = Rol::all();

        return [
            'roles' => $roles_->toArray(),
            'rolUser' => $usuarioRolId,
        ];
    }

    /**
     * Obtener estructura de menú del usuario según su rol asignado.
     *
     * @param int $usuarioId
     * @return array
     */
    public function menuUsuario($usuarioId)
    {
        $rolUser_ = RolUser::where('usuario_id', $usuarioId)->first();

        if ($rolUser_) {
            $rolId = $rolUser_->rol_id;
            $menuRol_ = MenuRol::where('rol_id', $rolId)->where('check', true)->pluck('menu_id');
        } else {
            $menuRol_ = collect([]);
        }

        $menus_ = Menu::with(['subMenuN1' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->whereNull('menu_id')->orderBy('order', 'asc')->get();

        $menusResp = [];
        foreach ($menus_ as $value) {
            $subMenus = $value->subMenuN1;
            $subMenusActivos = $this->filtrarSubmenusActivos($subMenus, $menuRol_);

            if (count($subMenusActivos) > 0) {
                $value->sub_menu = $subMenusActivos;
                $menusResp[] = $value;
            }
        }

        return ['menus' => $menusResp];
    }

    /**
     * Alias de compatibilidad para menú de usuario.
     */
    public function menuAcopio($usuarioId)
    {
        return $this->menuUsuario($usuarioId);
    }

    /**
     * Obtener todos los menús y su estado de activación para un rol específico.
     *
     * @param int $rolId
     * @return array
     */
    public function menuRol($rolId)
    {
        $menuRol_ = MenuRol::where('rol_id', $rolId)->where('check', true)->pluck('menu_id');
        $menus_ = Menu::with('subMenuN1')->whereNull('menu_id')->orderBy('order', 'asc')->get();

        $menusResp = [];
        foreach ($menus_ as $value) {
            $subMenus = $value->subMenuN1;
            $subM_ = $this->verificaEstadoSubmenu($subMenus, $menuRol_);
            $value->sub_menu = $subM_;
            $value->progreso = $this->progresoMenu($subM_);
            $menusResp[] = $value;
        }

        return ['menus' => $menusResp];
    }

    public function progresoMenu($menus)
    {
        $total = count($menus);
        $countActive = 0;

        foreach ($menus as $value) {
            if (!empty($value->active)) {
                $countActive++;
            }
        }

        if ($total == 0 || $countActive == 0) {
            return ['porcentaje' => 0, 'countActive' => 0, 'total' => $total];
        }

        $resp = ($countActive / $total) * 100;
        return ['porcentaje' => round($resp, 2), 'countActive' => $countActive, 'total' => $total];
    }

    public function verificaEstadoSubmenu($subMenus, $arrayMenuUser)
    {
        $resp = [];
        if (!is_array($subMenus)) {
            $menuArray = $arrayMenuUser->toArray();
            foreach ($subMenus as $value) {
                $value->active = in_array($value->id, $menuArray);
                $resp[] = $value;
            }
        }
        return $resp;
    }

    public function filtrarSubmenusActivos($subMenus, $arrayMenuUser)
    {
        $resp = [];
        if (!is_array($subMenus)) {
            $menuArray = $arrayMenuUser->toArray();
            foreach ($subMenus as $value) {
                if (in_array($value->id, $menuArray)) {
                    $value->active = null;
                    $resp[] = $value;
                }
            }
        }
        return $resp;
    }

    public function usuario_rol()
    {
        try {
            $users = User::with('roles', 'rolPersmisos')->get();
            return response()->json([
                'success' => true,
                'mensaje' => 'Listado de usuarios',
                'data' => $users,
            ]);
        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'mensaje' => $ex->getMessage()], 500);
        }
    }
}
