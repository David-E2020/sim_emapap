<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('insumos.tipo_documentos_insumos')->insert(
            [
                [
                    'tipo_movimiento' => 1,
                    'nombre' => 'Compra de Materia Prima',
                    'descripcion' => 'Incluye todas las compras de materias primas para la producción',
                    'codigo' => 'cmp',
                    'usuario_id' => 1
                ],

                [
                    'tipo_movimiento' => 1,
                    'nombre' => 'Importación de Materias Primas',
                    'descripcion' => 'Registro de materias primas importadas de otros países',
                    'codigo' => 'imp',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 1,
                    'nombre' => 'Limpieza y Preparación',
                    'descripcion' => 'Movimiento posterior a la recepción para limpiar y preparar las materias primas.',
                    'codigo' => 'lip',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 1,
                    'nombre' => 'Ajustes de Inventario',
                    'descripcion' => 'Incluye ajustes por sobrantes, diferencias de balanza y otros factores',
                    'codigo' => 'ain',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 1,
                    'nombre' => 'Reposición de Inventario',
                    'descripcion' => 'Reposición regular para mantener niveles adecuados de existencias.',
                    'codigo' => 'rin',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 0,
                    'nombre' => 'Orden de Producción',
                    'descripcion' => 'Utilización de materias primas para la producción de productos finales, Producción de Productos Transformados.',
                    'codigo' => 'orp',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 0,
                    'nombre' => 'Venta de Productos Transformados',
                    'descripcion' => 'Salida de productos transformados para su venta a los clientes.',
                    'codigo' => 'vpt',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 0,
                    'nombre' => 'Merma y Pérdidas',
                    'descripcion' => 'Incluye merma técnica, pérdida por humedad y pérdidas durante el transporte.',
                    'codigo' => 'mp',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 0,
                    'nombre' => 'Devoluciones y Ajustes de Inventario',
                    'descripcion' => 'Registro de productos devueltos y ajustes necesarios en el inventario.',
                    'codigo' => 'dai',
                    'usuario_id' => 1
                ],
                [


                    'tipo_movimiento' => 0,
                    'nombre' => 'Desecho y Eliminación',
                    'descripcion' => 'Manejo de productos obsoletos o dañados que no pueden ser utilizados o vendidos.',
                    'codigo' => 'dee',
                    'usuario_id' => 1
                ]
            ]
        );
    }
}
