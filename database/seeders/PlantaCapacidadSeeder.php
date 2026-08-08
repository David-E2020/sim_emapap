<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;


class PlantaCapacidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('planta_capacidades')->insert([
            [
                'cap_planta_id' => 10,
                'cap_punto_id' => 1,
                'cap_planta_capacidad' => 100000,
                'cap_planta_capacidad_saldo' => 50000,
                'cap_punto_capacidad' => 10000,
                'cap_usr_registrado' => 1,
                'created_at' => 'now()',
            ]
        ]);
    }
}
