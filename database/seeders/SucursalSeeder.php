<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('logistica.sucursales')->insert([

            'nombre'=>'Casa Matriz',
            'codigo'=>0,
            'descripcion'=>''
            

        ]);
    }
}



