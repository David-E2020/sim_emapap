<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use App\Models\Correspondencia\PlantillaDocumento;
use Illuminate\Database\Seeder;

class PlantillasDocumentosSeeder extends Seeder
{
    public function run(): void
    {
        $plantillas = [
            [
                'nombre' => 'Memorándum Institucional',
                'sigla' => 'MEM',
                'version' => 1,
                'param_tipo_plantilla' => 'INTERNO',
                'param_validez_legal' => 'PIN_ELECTRONICO',
                'multiples_para' => true,
                'cabecera_html' => '<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px;"><h3>EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA</h3><p>MEMORÁNDUM INTERNO</p></div>',
                'cuerpo_base' => '<p>Mediante el presente memorándum, se comunica a su autoridad que en el marco de las atribuciones conferidas...</p>',
                'pie_html' => '<div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 10px;">EMAPA - Construyendo la soberanía alimentaria de Bolivia.</div>',
                'config_pagina' => ['formato' => 'CARTA', 'margen_superior' => 25, 'margen_inferior' => 25, 'orientacion' => 'VERTICAL'],
            ],
            [
                'nombre' => 'Informe Técnico Circunstanciado',
                'sigla' => 'INF',
                'version' => 1,
                'param_tipo_plantilla' => 'INTERNO',
                'param_validez_legal' => 'PIN_ELECTRONICO',
                'multiples_para' => false,
                'cabecera_html' => '<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px;"><h3>EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA</h3><p>INFORME TÉCNICO OFICIAL</p></div>',
                'cuerpo_base' => '<h4>1. ANTECEDENTES</h4><p>En fecha reciente se procedió con la inspección y verificación...</p><h4>2. ANÁLISIS TÉCNICO</h4><p>De acuerdo a las pruebas y métricas recopiladas...</p><h4>3. CONCLUSIONES Y RECOMENDACIONES</h4><p>Se recomienda la aprobación inmediata de las gestiones solicitadas.</p>',
                'pie_html' => '<div style="margin-top: 30px; border-top: 1px solid #ccc; padding-top: 10px; font-size: 10px;">Documento Técnico Oficial EMAPA.</div>',
                'config_pagina' => ['formato' => 'CARTA', 'margen_superior' => 25, 'margen_inferior' => 25, 'orientacion' => 'VERTICAL'],
            ],
            [
                'nombre' => 'Nota Interna de Coordinación',
                'sigla' => 'NI',
                'version' => 1,
                'param_tipo_plantilla' => 'INTERNO',
                'param_validez_legal' => 'PIN_ELECTRONICO',
                'multiples_para' => true,
                'cabecera_html' => '<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px;"><h3>EMAPA</h3><p>NOTA INTERNA DE COMUNICACIÓN</p></div>',
                'cuerpo_base' => '<p>Por medio de la presente, tengo a bien dirigirme a usted con el propósito de coordinar las actividades de...</p>',
                'pie_html' => '<div style="margin-top: 20px; font-size: 10px;">EMAPA 2026.</div>',
                'config_pagina' => ['formato' => 'CARTA', 'margen_superior' => 20, 'margen_inferior' => 20, 'orientacion' => 'VERTICAL'],
            ],
            [
                'nombre' => 'Circular General de Cumplimiento',
                'sigla' => 'CIR',
                'version' => 1,
                'param_tipo_plantilla' => 'CIRCULAR',
                'param_validez_legal' => 'PIN_ELECTRONICO',
                'multiples_para' => true,
                'cabecera_html' => '<div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px;"><h3>EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS - EMAPA</h3><p>CIRCULAR INFORMATIVA GENERAL</p></div>',
                'cuerpo_base' => '<p>Se pone a conocimiento de todo el personal dependiente de la Empresa de Apoyo a la Producción de Alimentos que...</p>',
                'pie_html' => '<div style="margin-top: 20px; font-size: 10px;">Dirección Ejecutiva EMAPA.</div>',
                'config_pagina' => ['formato' => 'CARTA', 'margen_superior' => 25, 'margen_inferior' => 25, 'orientacion' => 'VERTICAL'],
            ],
        ];

        foreach ($plantillas as $pl) {
            $plantilla = PlantillaDocumento::updateOrCreate(
                ['sigla' => $pl['sigla'], 'version' => $pl['version']],
                [
                    'nombre' => $pl['nombre'],
                    'param_tipo_plantilla' => $pl['param_tipo_plantilla'],
                    'param_validez_legal' => $pl['param_validez_legal'],
                    'multiples_para' => $pl['multiples_para'],
                    'cabecera_html' => $pl['cabecera_html'],
                    'cuerpo_base' => $pl['cuerpo_base'],
                    'pie_html' => $pl['pie_html'],
                    'config_pagina' => $pl['config_pagina'],
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );

            // Componentes base
            $componentes = [
                ['nombre' => 'MEMBRETE_OFICIAL', 'tipo_componente' => 'HTML', 'orden' => 1, 'es_obligatorio' => true],
                ['nombre' => 'DATOS_DESTINATARIO_A_DE', 'tipo_componente' => 'DESTINATARIOS', 'orden' => 2, 'es_obligatorio' => true],
                ['nombre' => 'REFERENCIA_ASUNTO', 'tipo_componente' => 'TEXTO', 'orden' => 3, 'es_obligatorio' => true],
                ['nombre' => 'CUERPO_DOCUMENTO', 'tipo_componente' => 'HTML', 'orden' => 4, 'es_obligatorio' => true],
                ['nombre' => 'BLOQUE_FIRMAS_QR', 'tipo_componente' => 'FIRMAS', 'orden' => 5, 'es_obligatorio' => true],
            ];

            foreach ($componentes as $comp) {
                $plantilla->componentes()->updateOrCreate(
                    ['id_plantilla' => $plantilla->id, 'nombre' => $comp['nombre']],
                    [
                        'tipo_componente' => $comp['tipo_componente'],
                        'orden' => $comp['orden'],
                        'es_obligatorio' => $comp['es_obligatorio'],
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CREAR',
                        '_usuario_creacion' => 1,
                        '_fecha_creacion' => now(),
                    ]
                );
            }
        }
    }
}
