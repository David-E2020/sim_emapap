<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use App\Models\Parametrica;
use Illuminate\Database\Seeder;

class CorrespondenciaParametricasSeeder extends Seeder
{
    public function run(): void
    {
        $parametricas = [
            // PROVEÍDOS DE DERIVACIÓN
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_01', 'param_nombre' => 'PASE A SUS EFECTOS', 'param_descripcion' => 'Para prosecución de trámite según normativa vigente'],
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_02', 'param_nombre' => 'PARA SU CONOCIMIENTO Y FINES CONSIGUIENTES', 'param_descripcion' => 'Para conocimiento institucional'],
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_03', 'param_nombre' => 'PARA INFORME TÉCNICO CIRCUNSTANCIADO', 'param_descripcion' => 'Elaborar informe técnico de evaluación'],
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_04', 'param_nombre' => 'PREPARAR RESPUESTA OFICIAL', 'param_descripcion' => 'Redactar proyecto de respuesta o nota oficial'],
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_05', 'param_nombre' => 'PROCEDER SEGÚN REGLAMENTO', 'param_descripcion' => 'Aplicar normativa interna EMAPA'],
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_06', 'param_nombre' => 'ATENCIÓN URGENTE PRIORITARIA', 'param_descripcion' => 'Atención en plazo máximo de 24 horas'],
            ['param_tabla' => 'TABLA_LONDRA_PROVEIDOS', 'param_codigo' => 'PROV_07', 'param_nombre' => 'ARCHIVAR EXPEDIENTE', 'param_descripcion' => 'Trámite concluido, remitir a archivo central'],

            // TIPOS DOCUMENTALES
            ['param_tabla' => 'TABLA_LONDRA_TIPOS_DOCUMENTO', 'param_codigo' => 'MEM', 'param_nombre' => 'MEMORÁNDUM', 'param_descripcion' => 'Comunicación interna de cumplimiento obligatorio o asignación'],
            ['param_tabla' => 'TABLA_LONDRA_TIPOS_DOCUMENTO', 'param_codigo' => 'INF', 'param_nombre' => 'INFORME TÉCNICO', 'param_descripcion' => 'Documento de análisis técnico, financiero o legal'],
            ['param_tabla' => 'TABLA_LONDRA_TIPOS_DOCUMENTO', 'param_codigo' => 'NI', 'param_nombre' => 'NOTA INTERNA', 'param_descripcion' => 'Comunicación administrativa entre dependencias de EMAPA'],
            ['param_tabla' => 'TABLA_LONDRA_TIPOS_DOCUMENTO', 'param_codigo' => 'CIR', 'param_nombre' => 'CIRCULAR GENERAL', 'param_descripcion' => 'Instrucción o directriz de alcance general a toda la entidad'],
            ['param_tabla' => 'TABLA_LONDRA_TIPOS_DOCUMENTO', 'param_codigo' => 'CAR', 'param_nombre' => 'CARTA EXTERNA', 'param_descripcion' => 'Correspondencia dirigida a ministerios, gobernaciones o privados'],
            ['param_tabla' => 'TABLA_LONDRA_TIPOS_DOCUMENTO', 'param_codigo' => 'RES', 'param_nombre' => 'RESOLUCIÓN ADMINISTRATIVA', 'param_descripcion' => 'Disposición normativa de máxima autoridad ejecutiva'],

            // PRIORIDADES
            ['param_tabla' => 'TABLA_LONDRA_PRIORIDADES', 'param_codigo' => 'PRIOR_URGENTE', 'param_nombre' => 'URGENTE', 'param_descripcion' => '24 horas de plazo'],
            ['param_tabla' => 'TABLA_LONDRA_PRIORIDADES', 'param_codigo' => 'PRIOR_ALTA', 'param_nombre' => 'ALTA', 'param_descripcion' => '48 horas de plazo'],
            ['param_tabla' => 'TABLA_LONDRA_PRIORIDADES', 'param_codigo' => 'PRIOR_MEDIA', 'param_nombre' => 'MEDIA', 'param_descripcion' => '72 horas de plazo'],
            ['param_tabla' => 'TABLA_LONDRA_PRIORIDADES', 'param_codigo' => 'PRIOR_BAJA', 'param_nombre' => 'BAJA', 'param_descripcion' => '5 días de plazo'],

            // TIPOS DE ATENCIÓN EN VENTANILLA
            ['param_tabla' => 'TABLA_LONDRA_TIPO_VENTANILLA', 'param_codigo' => 'VENT_FISICA', 'param_nombre' => 'VENTANILLA FÍSICA', 'param_descripcion' => 'Atención presencial en mesón de correspondencia'],
            ['param_tabla' => 'TABLA_LONDRA_TIPO_VENTANILLA', 'param_codigo' => 'VENT_DIGITAL', 'param_nombre' => 'VENTANILLA DIGITAL', 'param_descripcion' => 'Ingreso de trámites por portal web ciudadano'],
        ];

        foreach ($parametricas as $index => $p) {
            Parametrica::updateOrCreate(
                ['param_tabla' => $p['param_tabla'], 'param_codigo' => $p['param_codigo']],
                [
                    'param_nombre' => $p['param_nombre'],
                    'param_descripcion' => $p['param_descripcion'],
                    'param_valor' => $index + 1,
                    'param_estado' => 'A',
                    'param_usr_registrado' => 1,
                ]
            );
        }
    }
}
