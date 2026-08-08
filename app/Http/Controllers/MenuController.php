<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\MenuRol;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */




    public function index()
    {
        $menus=Menu::with(['subMenuN1'=>function($query){
                  $query->orderBy('order', 'asc');
            }])->where('menu_id','=', null)->orderBy('order', 'asc')->get();
        return $menus;
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
      return  Menu::create($input);
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
         $menu=Menu::find($id);
         $menu->label= $input['label'];
         $menu->icon= $input['icon'];
         $menu->route= $input['route'];
         $menu->save();
         return $menu;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        $menu_= Menu::where('menu_id', $id)->get();
        $total= count($menu_);


        if ($total!=0) {
         $result= array(
                    'code' => 406,
                    'message'=>"El menu tiene asignado sub menus "
                     );
            return response()->json($result,406);
        }else{
            $menuRol_= MenuRol::where('menu_id', $id)->get();
            $totalMenuRol= count($menuRol_);

            if ($totalMenuRol!=0) {
                $result= array('code' => 406,
                            'message'=>"El menu esta asignado a un rol "
                            );
                return response()->json($result,406);
            }else{
                $menu = Menu::find($id);
                $menu->delete();
                return "delete";
            }

            
        }

       
    }


    public function cambiarOrden(Request $request)
    {
        $input = $request->all();

        $draggedId= $input['dragged_id'];
        $draggedOrder= $input['dragged_order'];

        $relatedId= $input['related_id'];
        $relatedOrder= $input['related_order'];

        $menu1= Menu::find($draggedId);
        $menu2= Menu::find($relatedId);

        $order1= $menu1->order;
        $order2= $menu2->order;

        $menu2->order =  $order1;
        $menu1->order =  $order2;
        
        $menu2->save();
        $menu1->save();
            
        return $menu1;
    }
}
