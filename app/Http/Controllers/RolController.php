<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\RolUser;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $roles=Rol::orderBy('id', 'asc')->get();
        return $roles;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['guard_name'] = 'api';
        return Rol::create($input);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
         $rol=Rol::find($id);
         $rol->name= $input['name'];
         $rol->save();

         return $rol;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

       $rolUser= RolUser::where('rol_id', $id)->get();
       $total= count($rolUser);

        if ($total!=0) {
            $result= array(
            'code' => 406,
            'message'=>"El rol esta asignado a un usuario "
             );
            return response()->json($result,406);
        } else {
            $rol = Rol::find($id);
            $rol->delete();
            return "delete";
        }



        
    }
}
