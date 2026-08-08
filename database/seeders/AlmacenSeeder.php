<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlmacenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('almacenes')->insert([
            'alm_nombre' => 'Almacén de exportaciones el alto',
            'alm_linea_id'=>1
        ]);
    }
}
