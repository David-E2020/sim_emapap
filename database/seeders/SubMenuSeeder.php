<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '1',
        
            'nombre_permiso' => 'SIAT',
            'orden' => '1'
         ]);

         DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '1',
        
            'nombre_permiso' => 'Emitir Factura',
            'orden' => '2'
         ]);


         //

         DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '2',
        
            'nombre_permiso' => 'Directo',
            'orden' => '1'
         ]);

         DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '2',
        
            'nombre_permiso' => 'Almacén',
            'orden' => '1'
         ]);

         //
        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '3',
        
            'nombre_permiso' => 'Salida',
            'orden' => '1'
         ]);
        //

        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '4',
        
            'nombre_permiso' => 'Contratos',
            'orden' => '1'
         ]);
        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '4',
        
            'nombre_permiso' => 'Comprador',
            'orden' => '2'
         ]);

        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '4',
        
            'nombre_permiso' => 'Clientes',
            'orden' => '3'
         ]);

        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '4',
        
            'nombre_permiso' => 'Incoterm',
            'orden' => '4'
         ]);

        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '4',
        
            'nombre_permiso' => 'Datos Registro',
            'orden' => '5'
         ]);


        //

        DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '5',
        
            'nombre_permiso' => 'Usuarios',
            'orden' => '1'
         ]);

         DB::table('logistica.sub_menus')->insert([
            'icon' => 'icon',
            'route' => 'yyyyyyyy',
            'type' => 'SubMenu',
            'menu_id' => '5',
        
            'nombre_permiso' => 'Accesos y permisos',
            'orden' => '1'
         ]);
    }
}
