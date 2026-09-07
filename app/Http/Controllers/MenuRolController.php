<?php

namespace App\Http\Controllers;

use App\Models\MenuRol;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MenuRolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {

        $input = $request->all();
        $menuId = $input['menu_id'];
        $rolId = $input['rol_id'];

        $menuRol_ = MenuRol::where('menu_id', $menuId)->where('rol_id', $rolId)->first();

        if ($menuRol_ != null) {
            $estadoCheck = $menuRol_->check;

            if (! $estadoCheck) {
                $menuRol_->check = true;
            } else {
                $menuRol_->check = false;
            }
            $menuRol_->save();

            return $menuRol_;
        } else {
            $rol = MenuRol::create($input);

            return $rol;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
