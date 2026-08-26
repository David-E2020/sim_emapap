<?php

declare(strict_types=1);

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use App\Services\Administracion\UserAccessService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly UserAccessService $userAccessService
    ) {}

    /**
     * Listado de usuarios activos con sus roles y permisos.
     */
    public function index(): JsonResponse
    {
        $users = User::with(['roles', 'permissions', 'rolPersmisos.rol'])
            ->where('usr_estado', 'A')
            ->orderBy('id', 'asc')
            ->get()
            ->makeHidden(['deleted_at', 'usr_archivo', 'usr_modificado', 'usr_registrado']);

        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Deshabilitar/quitar acceso de un usuario al sistema (Transaccional).
     */
    public function quitarSistema(int|string $id): JsonResponse
    {
        try {
            $this->userAccessService->revokeAccess((int)$id);

            return response()->json([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'message' => 'Se quitó el acceso al sistema correctamente',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Usuario no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            Log::error('Error al revocar acceso al sistema', [
                'user_id' => $id,
                'exception' => $ex->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'mensaje' => 'No se pudo revocar el acceso. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Habilitar/asignar acceso de un usuario al sistema con rol predeterminado (Transaccional).
     */
    public function agregarSistema(int|string $id): JsonResponse
    {
        try {
            $this->userAccessService->enableAccess((int)$id);

            return response()->json([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'message' => 'Se asignó el acceso al sistema correctamente',
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'mensaje' => 'Usuario no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            Log::error('Error al agregar acceso al sistema', [
                'user_id' => $id,
                'exception' => $ex->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'mensaje' => 'No se pudo asignar el acceso. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtener roles disponibles y rol actual de un usuario.
     */
    public function rolUser(int|string $userId): array
    {
        $rolUser_ = RolUser::where('usuario_id', (int)$userId)->first();
        $usuarioRolId = $rolUser_ ? $rolUser_->rol_id : null;
        $roles_ = Rol::all();

        return [
            'roles' => $roles_->toArray(),
            'rolUser' => $usuarioRolId,
        ];
    }

    /**
     * Obtener estructura de menú del usuario según su rol asignado.
     */
    public function menuUsuario(int|string $usuarioId): array
    {
        $rolUser_ = RolUser::where('usuario_id', (int)$usuarioId)->first();

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
    public function menuAcopio(int|string $usuarioId): array
    {
        return $this->menuUsuario($usuarioId);
    }

    /**
     * Obtener todos los menús y su estado de activación para un rol específico.
     */
    public function menuRol(int|string $rolId): array
    {
        $menuRol_ = MenuRol::where('rol_id', (int)$rolId)->where('check', true)->pluck('menu_id');
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

    public function progresoMenu(array|Collection|\Illuminate\Support\Collection $menus): array
    {
        $total = count($menus);
        $countActive = 0;

        foreach ($menus as $value) {
            if (!empty($value->active)) {
                $countActive++;
            }
        }

        if ($total === 0 || $countActive === 0) {
            return ['porcentaje' => 0, 'countActive' => 0, 'total' => $total];
        }

        $resp = ($countActive / $total) * 100;
        return ['porcentaje' => round($resp, 2), 'countActive' => $countActive, 'total' => $total];
    }

    public function verificaEstadoSubmenu(mixed $subMenus, mixed $arrayMenuUser): array
    {
        $resp = [];
        if (!is_array($subMenus)) {
            $menuArray = $arrayMenuUser->toArray();
            foreach ($subMenus as $value) {
                $value->active = in_array($value->id, $menuArray, true);
                $resp[] = $value;
            }
        }
        return $resp;
    }

    public function filtrarSubmenusActivos(mixed $subMenus, mixed $arrayMenuUser): array
    {
        $resp = [];
        if (!is_array($subMenus)) {
            $menuArray = $arrayMenuUser->toArray();
            foreach ($subMenus as $value) {
                if (in_array($value->id, $menuArray, true)) {
                    $value->active = null;
                    $resp[] = $value;
                }
            }
        }
        return $resp;
    }

    public function usuario_rol(): JsonResponse
    {
        try {
            $users = User::with(['roles', 'rolPersmisos'])->get();
            return response()->json([
                'success' => true,
                'mensaje' => 'Listado de usuarios',
                'data' => $users,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al listar usuario_rol', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'mensaje' => 'Error al obtener usuarios.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
