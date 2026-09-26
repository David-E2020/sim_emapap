<?php

declare(strict_types=1);

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rrhh\PersonalController;
use App\Models\AuditLog;
use App\Models\Menu;
use App\Models\MenuRol;
use App\Models\Rol;
use App\Models\RolUser;
use App\Models\User;
use App\Services\Administracion\UserAccessService;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UsuarioController extends Controller
{
    public function __construct(
        private readonly UserAccessService $userAccessService,
        private readonly AuditService $auditService
    ) {}

    /**
     * Listado de usuarios con sus roles y permisos (incluye activos e inactivos para gestión).
     */
    public function index(): JsonResponse
    {
        $users = User::with(['roles', 'permissions', 'rolPersmisos.rol'])
            ->orderBy('id', 'asc')
            ->get()
            ->makeHidden(['deleted_at', 'usr_archivo', 'usr_modificado', 'usr_registrado']);

        return response()->json($users, Response::HTTP_OK);
    }

    /**
     * Crear un nuevo usuario en el sistema con rol inicial opcional.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'usr_usuario' => 'required|string|max:50|unique:users,usr_usuario',
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:6',
            'rol_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $user = new User;
            $user->usr_usuario = trim($request->input('usr_usuario'));
            $user->name = trim($request->input('name'));
            $user->email = strtolower(trim($request->input('email')));
            $user->password = Hash::make($request->input('password'));
            $user->usr_estado = 'A';
            $user->usr_registrado = auth()->id();
            $user->save();

            // Si se seleccionó rol, asignar accesos de inmediato
            if ($request->filled('rol_id')) {
                $this->userAccessService->enableAccess($user->id, (int) $request->input('rol_id'));
            }

            // Auditoría inmutable
            $this->auditService->log(
                event: 'user_created',
                model: $user,
                newValues: [
                    'usr_usuario' => $user->usr_usuario,
                    'name' => $user->name,
                    'email' => $user->email,
                    'rol_id' => $request->input('rol_id'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente.',
                'data' => $user->load(['roles', 'permissions', 'rolPersmisos.rol']),
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar usuario', [
                'request' => $request->except(['password']),
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo registrar el usuario. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ver el detalle completo de un usuario (Perfil, roles, permisos y actividad).
     */
    public function show(int|string $id): JsonResponse
    {
        try {
            $user = User::with(['roles.permissions', 'permissions', 'rolPersmisos.rol'])
                ->findOrFail((int) $id);

            // Últimos eventos de auditoría relacionados con este usuario
            $recentAuditLogs = AuditLog::where('auditable_type', User::class)
                ->where('auditable_id', $user->id)
                ->orWhere('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'user' => $user,
                'recent_logs' => $recentAuditLogs,
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            Log::error('Error al consultar detalle de usuario', ['id' => $id, 'exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al consultar el usuario.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Actualizar datos generales de un usuario.
     */
    public function update(Request $request, int|string $id): JsonResponse
    {
        $user = User::findOrFail((int) $id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $oldValues = $user->only(['name', 'email']);

            $user->name = trim($request->input('name'));
            $user->email = strtolower(trim($request->input('email')));
            $user->usr_modificado = auth()->id();

            if ($request->filled('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            $user->save();

            $this->auditService->log(
                event: 'user_profile_updated',
                model: $user,
                oldValues: $oldValues,
                newValues: $user->only(['name', 'email'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente.',
                'data' => $user->load(['roles', 'permissions']),
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al actualizar usuario', ['id' => $id, 'exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al actualizar usuario.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deshabilitar/quitar acceso de un usuario al sistema (Transaccional).
     */
    public function quitarSistema(int|string $id): JsonResponse
    {
        try {
            $this->userAccessService->revokeAccess((int) $id);

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
                'exception' => $ex->getMessage(),
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
            $this->userAccessService->enableAccess((int) $id);

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
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'mensaje' => 'No se pudo asignar el acceso. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restablecer la contraseña de un usuario según el estándar institucional:
     * Si está vinculado a una persona/funcionario, usa sus iniciales + CI + "!!".
     * Si no tiene persona vinculada, usa su identificador institucional + "2026!!".
     */
    public function resetPasswordInstitucional(Request $request, int|string $id): JsonResponse
    {
        try {
            $user = User::with('persona')->findOrFail((int) $id);

            if ($user->persona) {
                $creds = PersonalController::generarCredencialesIniciales($user->persona);
                $passwordPlana = $creds['password'];
            } else {
                $usuarioBase = ucfirst(strtolower(preg_replace('/[^A-Za-z0-9]/', '', $user->usr_usuario)));
                $passwordPlana = $usuarioBase . '2026!!';
            }

            $user->update([
                'password' => Hash::make($passwordPlana),
                'usr_estado' => 'A',
            ]);

            $this->auditService->log(
                event: 'user_password_reset_institutional',
                model: $user,
                newValues: [
                    'usr_usuario' => $user->usr_usuario,
                    'reset_by' => auth()->id() ?? 1,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Contraseña institucional restablecida exitosamente.',
                'credenciales' => [
                    'nombre' => $user->name,
                    'usuario' => $user->usr_usuario,
                    'password' => $passwordPlana,
                ],
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Throwable $ex) {
            Log::error('Error al restablecer contraseña institucional', [
                'user_id' => $id,
                'exception' => $ex->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo restablecer la contraseña. Intente nuevamente.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtener roles disponibles y rol actual de un usuario.
     */
    public function rolUser(int|string $userId): array
    {
        $rolUser_ = RolUser::where('usuario_id', (int) $userId)->first();
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
        $rolUser_ = RolUser::where('usuario_id', (int) $usuarioId)->first();

        if ($rolUser_) {
            $rolId = $rolUser_->rol_id;
            // Si el rol es Administrador General (ID 1), asegurar acceso total a todos los menús activos
            if ($rolId === 1) {
                $menuRol_ = Menu::where('estado', true)->pluck('id');
            } else {
                $menuRol_ = MenuRol::where('rol_id', $rolId)->where('check', true)->pluck('menu_id');
            }
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
        $menuRol_ = MenuRol::where('rol_id', (int) $rolId)->where('check', true)->pluck('menu_id');
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
            if (! empty($value->active)) {
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
        if (! is_array($subMenus)) {
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
        if (! is_array($subMenus)) {
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
