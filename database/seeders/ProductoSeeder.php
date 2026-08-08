<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('logistica.productos')->insert([
           'prod_codigo'=>'EBA-MDM-O-20K',
           'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
           'prod_nombre' => 'CASTAÑA',
          // 'prod_tipo' => 'MEDIUM',
          // 'prod_calidad' => 'ORGANICA',
           'prod_presentacion'=>44.00,
           'linea_id'=>1,
           'prod_formato_presentacion'=>'CAJA',
           'prod_usr_registrado' => 1
        ]);

        DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-MGT-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
            //'prod_tipo' => 'MIDGET',
           //'prod_calidad' => 'ORGANICA',
           'prod_presentacion'=>44.00,
           'linea_id'=>1,
           'prod_formato_presentacion'=>'CAJA',
           'prod_usr_registrado' => 1
        ]);

       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-LGE-O-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'LARGE',
           // 'prod_calidad' => 'ORGANICA',
            'prod_presentacion'=>44.00,
           'linea_id'=>1,
           'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

      DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-TNY-O-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
          //  'prod_tipo' => 'TINNY',
          //  'prod_calidad' => 'ORGANICA',
            'prod_presentacion'=>44.00,
           'linea_id'=>1,
           'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-CPD-O-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
          //  'prod_tipo' => 'CHIPPED',
          //  'prod_calidad' => 'ORGANICA',
            'prod_presentacion'=>44.00,
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

      DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-BKN-O-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'BROKEN',
            //'prod_calidad' => 'ORGANICA',
            'prod_presentacion'=>44.00,
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-BKND-O-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
          //  'prod_tipo' => 'BROKEN D',
            //'prod_calidad' => 'ORGANICO',
            'prod_presentacion'=>44.00,
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-MDM-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
          //  'prod_tipo' => 'MEDIUM',
          //  'prod_calidad' => 'CONVENCIONAL',
            'prod_presentacion'=>44.00,
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-MGT-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'MIDGET',
          //  'prod_calidad' => 'CONVENCIONAL',
            'prod_presentacion'=>44.00,
           'linea_id'=>1,
           'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

      DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-LGE-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'LARGE',
           // 'prod_calidad' => 'CONVENCIONAL',
            'prod_presentacion'=>44.00,
           'linea_id'=>1,
           'prod_formato_presentacion'=>'CAJA',
           'prod_usr_registrado' => 1
        ]);


       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-TNY-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'TINNY',
            //'prod_calidad' => 'CONVENCIONAL',
            'prod_presentacion'=>44.00,
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);


        DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-CPD-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'CHIPPED',
           // 'prod_calidad' => 'CONVENCIONAL',
            'prod_presentacion'=>44.00,
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);

       DB::table('logistica.productos')->insert([
            'prod_codigo'=>'EBA-BKN-C-20K',
            'prod_desc' => 'BOLIVIAN SHELLED BRAZIL NUT',
            'prod_nombre' => 'CASTAÑA',
           // 'prod_tipo' => 'BROKEN',
          //  'prod_calidad' => 'CONVENCIONAL',
            'prod_presentacion'=>44.00,
           //'prod_presentacion_detalle'=>'20KG',
            'linea_id'=>1,
            'prod_formato_presentacion'=>'CAJA',
            'prod_usr_registrado' => 1
        ]);






    }
}
