<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngresoArticuloInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('insumos.ingresos_insumos')->insert(
            [
                [
                    'planta_id' => 1,
                    'proveedor_id' => 1,
                    'user_id' => 1,
                    'ruta_adjunto' => 'pdf.pdf',
                    'numero_proceso' => 'EMAPA /CM/MEMO. RR N347/2023',
                    'fecha_factura' => null,
                    'motivo' => '"SALDO INICIAL"',
                    'costo_total' => 0.00,
                    'numero_acta' => 7780,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 1,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'MENDOZA FLORES JORGE LISANDRO NIT.: 4278043019 COD. AUTORIZACION.: 124B7305CABC2633AA2F6250B06A8E77354D4F5D448107015882AEFD74',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => null,
                    'created_at' => '2023-10-11 11:25:25'
                ],
                [

                    'planta_id' => 2,
                    'proveedor_id' => 2,
                    'user_id' => 2,
                    'ruta_adjunto' => null,
                    'numero_proceso' => 'EMAPA - CD N 360/2023',
                    'fecha_factura' => null,
                    'motivo' => 'ADQ DE MATERIALES ELECTRICOS Y ELECTRONEUMATICO-PLANTA SAN ANDRES',
                    'costo_total' => null,
                    'numero_acta' => 7782,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 1,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'ICONTROL Ltda.-NIT146610025-AUTORIZACION N°262401300676579',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => null,
                    'created_at' => '2023-10-11 12:34:55'
                ],
                [
                    'planta_id' => 3,
                    'proveedor_id' => 3,
                    'user_id' => 3,
                    'ruta_adjunto' => null,
                    'numero_proceso' => 'EMAPA - CD N 477/2023',
                    'fecha_factura' => null,
                    'motivo' => 'ADQ DE MANGAS DE LONA PARA EL COOL SEED-PLANTA SAN PEDRO',
                    'costo_total' => null,
                    'numero_acta' => 7784,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 1,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'IMANELECTRO SRL-NIT 388037023-COD AUTORIZACION 1A8CF230EB0D886CF0E1998F6B2090F7F62DDD004898E9A0D6CBEFD74',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => null,
                    'created_at' => '2023-10-11 12:34:55'
                ],
                [
                    'planta_id' => 1,
                    'proveedor_id' => 2,
                    'user_id' => 1,
                    'ruta_adjunto' => 'pdf.pdf',
                    'numero_proceso' => 'EMAPA - CD N 257/2023',
                    'fecha_factura' => null,
                    'motivo' => 'ADQUISICION DE IMPLEMENTOS DE SEGURIDAD INDUSTRIAL EPP.S PARA LA PLANTA SAN JULIAN',
                    'costo_total' => null,
                    'numero_acta' => 7686,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 2,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'TECNOLOGIA E INNOVACION S.R.L., NIT.: 318934020 COD. AUTORIZACIÓN.: 15D2859BD6369F9D264759F70718AB296322A3A58A6FDB2479BCBFD74',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => 1,
                    'created_at' => '2023-10-11 12:34:55'
                ],
                [
                    'planta_id' => 2,
                    'proveedor_id' => 6,
                    'user_id' => 1,
                    'ruta_adjunto' => 'pdf.pdf',
                    'numero_proceso' => 'EMAPA - CM N 310/2023',
                    'fecha_factura' => null,
                    'motivo' => 'ADQ. DE BOTAS PARA EL PERSONAL DE LA GERENCIA DE PRODUCCION DEL PROGRAMA PISCICOLA GP/COM081/2023',
                    'costo_total' => 0.00,
                    'numero_acta' => 7653,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 2,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'LAVOISIER & SOLUCIONES - NIT 8041852015 - COD AUTORIZACION 10168887F3811A AUTORIZACION',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => null,
                    'created_at' => '2023-10-11 12:34:55'
                ],

                [
                    'planta_id' => 3,
                    'proveedor_id' => 5,
                    'user_id' => 1,
                    'ruta_adjunto' => 'pdf.pdf',
                    'numero_proceso' => 'EMAPA - CD N 373/2023',
                    'fecha_factura' => null,
                    'motivo' => 'ADDQ DE ACCESORIOS NEUMATICOSY ELECTRONEUMATICOS - PLANTA CARACOLLO',
                    'costo_total' => 0.00,
                    'numero_acta' => 7648,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 2,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'SMC AUTOMATION BOLIVIA SRL - NIT 1030707029 - COD AUTORIZACION 46861AC108B2E0E0E00B42DDD30689689C4036871A31D813364F4DDFD74',
                    'fecha_ingreso' => '2023-10-11 12:34:55',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => 2,
                    'created_at' => '2023-10-11 12:34:55'
                ],
                [
                    'planta_id' => 1,
                    'proveedor_id' => 3,
                    'user_id' => 1,
                    'ruta_adjunto' => 'pdf.pdf',
                    'numero_proceso' => 'EMAPA-ANPE N 045/2023',
                    'fecha_factura' => null,
                    'motivo' => '"COMPRA DE MATERIAL DE ESCRITORIO PARA LAS GERENCIAS  DE LA (EMAPA) 1 ER. TRIMESTRE . GEST. 2023 ..',
                    'costo_total' => null,
                    'numero_acta' => 7346,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 3,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'MANUFACTURAS ATZUMY  NIT.: 4364871016   COD. AUTORIZACION .: 101C23C8CBF01A ',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => null,
                    'created_at' => '2023-10-11 12:34:55'
                ],
                [
                    'planta_id' => 1,
                    'proveedor_id' => 5,
                    'user_id' => 1,
                    'ruta_adjunto' => 'pdf.pdf',
                    'numero_proceso' => 'EMAPA - CM N 137/2023',
                    'fecha_factura' => null,
                    'motivo' => 'ADQUISICION DE UTILES DE ESCRITORIO Y OFICIN - PLANT. SAN ANDRES',
                    'costo_total' => null,
                    'numero_acta' => 7238,
                    'tipo_ingreso' => 1,
                    'numero_documento' => 4,
                    'estado_id' => 1,
                    'fecha_acta' => null,
                    'observaciones' => 'PROVEEDORA RODRIGUEZ NIT: 1921220013 CÓDIGO DE AUTORIZACION: 10154352FAF31A',
                    'fecha_ingreso' => '2023-10-11 00:00:00',
                    'c31_preventivo' => null,
                    'lote_id' => null,
                    'exclusivo_gerencia_id' => null,
                    'created_at' => '2023-10-11 12:34:55'
                ]
            ]
        );
    }
}
