<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class PuntoVentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       DB::table('logistica.punto_ventas')->insert([
            'nombre'=>'Casa Matriz',
            'sucursal_id'=>1,
            'codigo'=>0,
            'cuis'=> '33054AF8',//'84307E72',// '33054AF8'// '84307E72'
            
            'municipio'=>'La Paz',
            'telefono'=>'2145697-65151877',
            'direccion'=>'AVENIDA ARCE NRO. 2382, EDIFICIO HERMANOS MALDONADO PISO 1 DEPTO. 1, ZONA/BARRIO SOPOCACHI'

        ]);


      DB::table('logistica.punto_ventas')->insert([
            'nombre'=>'Planta',
            'sucursal_id'=>1,
            'codigo'=>1,
            'cuis'=> '8B02D97F',//'1E05C87E', //'8B02D97F'//'1E05C87E',

            'municipio'=>'La Paz',
            'telefono'=>'2145697-65151877',
            'direccion'=>'AVENIDA ARCE NRO. 2382, EDIFICIO HERMANOS MALDONADO PISO 1 DEPTO. 1, ZONA/BARRIO SOPOCACHI'

        ]);


    }
}
