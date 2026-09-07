<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuRol;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $menus = Menu::with(['subMenuN1' => function ($query) {
            $query->orderBy('order', 'asc');
        }])->where('menu_id', '=', null)->orderBy('order', 'asc')->get();

        return $menus;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        return Menu::create($input);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $menu = Menu::find($id);
        $menu->label = $input['label'];
        $menu->icon = $input['icon'];
        $menu->route = $input['route'];
        $menu->save();

        return $menu;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $menu_ = Menu::where('menu_id', $id)->get();
        $total = count($menu_);

        if ($total != 0) {
            $result = [
                'code' => 406,
                'message' => 'El menú tiene submenús asignados',
            ];

            return response()->json($result, 406);
        } else {
            $menuRol_ = MenuRol::where('menu_id', $id)->get();
            $totalMenuRol = count($menuRol_);

            if ($totalMenuRol != 0) {
                $result = [
                    'code' => 406,
                    'message' => 'El menú está asignado a un rol',
                ];

                return response()->json($result, 406);
            } else {
                $menu = Menu::find($id);
                $menu->delete();

                return 'delete';
            }
        }
    }

    public function cambiarOrden(Request $request)
    {
        $input = $request->all();

        $draggedId = $input['dragged_id'];
        $relatedId = $input['related_id'];

        $menu1 = Menu::find($draggedId);
        $menu2 = Menu::find($relatedId);

        $order1 = $menu1->order;
        $order2 = $menu2->order;

        $menu2->order = $order1;
        $menu1->order = $order2;

        $menu2->save();
        $menu1->save();

        return $menu1;
    }

    /**
     * Obtener lista de permisos de Spatie vinculados a un submenú
     */
    public function getPermisosSubmenu($id)
    {
        $menu = Menu::find($id);
        if (! $menu) {
            return response()->json(['permisos' => []]);
        }

        // Mapeo automático de ruta a prefijo de permiso
        $prefix = $this->getPrefixFromRoute($menu->route);
        $allPermissions = Permission::where('name', 'like', $prefix.'%')->get();

        return response()->json([
            'menu' => $menu,
            'prefix' => $prefix,
            'permisos' => $allPermissions,
        ]);
    }

    /**
     * Crear una nueva acción de permiso granular para un submenú
     */
    public function crearPermisoSubmenu(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer',
            'nombre_accion' => 'required|string',
        ]);

        $menu = Menu::find($request->menu_id);
        if (! $menu) {
            return response()->json(['success' => false, 'message' => 'Submenú no encontrado'], 404);
        }

        $prefix = $this->getPrefixFromRoute($menu->route);
        $cleanAction = strtolower(trim(str_replace(' ', '_', $request->nombre_accion)));
        $permissionName = $prefix.'.'.$cleanAction;

        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'api',
        ]);

        // Asignar automáticamente al Administrador General
        $adminRole = Role::where('name', 'Administrador General')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }

        return response()->json([
            'success' => true,
            'permiso' => $permission,
            'message' => 'Permiso granular creado correctamente',
        ]);
    }

    private function getPrefixFromRoute($route)
    {
        switch ($route) {
            case 'admin-usuario':
            case 'usuario':
                return 'admin.usuarios';
            case 'admin-menu':
            case 'menu':
                return 'admin.menus';
            case 'control_acceso':
                return 'admin.control_acceso';
            case 'parametrica':
                return 'parametricas';
            default:
                return strtolower(str_replace(['/', '-'], '.', $route ?: 'modulo.desconocido'));
        }
    }
}
