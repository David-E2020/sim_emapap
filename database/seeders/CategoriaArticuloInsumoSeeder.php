<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class CategoriaArticuloInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('insumos.categorias_articulos_insumos')->insert([
                 
                [
                'categoria_id' => 45,
                'planta_id' => 1,
                'articulo_id' => 12,
                'unidad_medida_id' => 1,
                'cantidad_predefinida' => 199,
                'porcentaje' => 100,
                'user_id' => 1,
                 ],
                
                [
                'categoria_id' => 47,
                'planta_id' => 1,
                'articulo_id' => 9,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 5,
                'porcentaje' => 1,
                'user_id' => 2,
                 ],
                
                [
                'categoria_id' => 44,
                'planta_id' => 2,
                'articulo_id' => 10,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 45,
                'planta_id' => 1,
                'articulo_id' => 3,
                'unidad_medida_id' => 1,
                'cantidad_predefinida' => 199,
                'porcentaje' => 100,
                'user_id' => 1,
                 ],
                
                [
                'categoria_id' => 46,
                'planta_id' => 1,
                'articulo_id' => 1,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 5,
                'porcentaje' => 1,
                'user_id' => 2,
                 ],
                
                [
                'categoria_id' => 45,
                'planta_id' => 2,
                'articulo_id' => 1,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 45,
                'planta_id' => 2,
                'articulo_id' => 2,
                'unidad_medida_id' => 1,
                'cantidad_predefinida' => 199,
                'porcentaje' => 100,
                'user_id' => 1,
                 ],
                
                [
                'categoria_id' => 45,
                'planta_id' => 1,
                'articulo_id' => 2,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 44,
                'planta_id' => 1,
                'articulo_id' => 11,
                'unidad_medida_id' => 1,
                'cantidad_predefinida' => 199,
                'porcentaje' => 100,
                'user_id' => 1,
                 ],
                
                [
                'categoria_id' => 43,
                'planta_id' => 2,
                'articulo_id' => 4,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' =>1 ,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 45,
                'planta_id' => 1,
                'articulo_id' => 1,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 43,
                'planta_id' => 1,
                'articulo_id' => 3,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 43,
                'planta_id' => 1,
                'articulo_id' => 5,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' =>1 ,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 43,
                'planta_id' => 1,
                'articulo_id' => 6,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                 ],
                
                [
                'categoria_id' => 43,
                'planta_id' => 1,
                'articulo_id' => 7,
                'unidad_medida_id' => 2,
                'cantidad_predefinida' => 3,
                'porcentaje' => 1,
                'user_id' => 3,
                ],     
                      
            ]);
    }
}
