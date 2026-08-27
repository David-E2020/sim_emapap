<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use App\Models\Correspondencia\Correlativo;
use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\FirmaAprobacion;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Correspondencia\PlantillaDocumento;
use App\Models\Correspondencia\Ventanilla;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Models\User;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use App\Services\Correspondencia\FirmaDigitalService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CorrespondenciaDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $citeService = new CiteGeneratorService();
        $workflowService = new DerivacionWorkflowService();
        $firmaService = new FirmaDigitalService();

        // 1. CREAR VENTANILLAS
        $regLPZ = Regional::where('sigla', 'LPZ')->first() ?: Regional::first();
        $regSCZ = Regional::where('sigla', 'SCZ')->first() ?: Regional::first();
        $unidadGG = UnidadOrganizacional::where('sigla', 'GG')->first() ?: UnidadOrganizacional::first();
        $unidadGAF = UnidadOrganizacional::where('sigla', 'GAF')->first() ?: UnidadOrganizacional::first();
        $unidadURRH = UnidadOrganizacional::where('sigla', 'URRH')->first() ?: UnidadOrganizacional::first();
        $unidadGCOM = UnidadOrganizacional::where('sigla', 'GCOM')->first() ?: UnidadOrganizacional::first();

        $ventanillaCentral = Ventanilla::updateOrCreate(
            ['nombre' => 'Ventanilla Central de Correspondencia - La Paz'],
            [
                'direccion' => 'Av. Mariscal Santa Cruz Edif. Centro de Comunicaciones La Paz Piso 15',
                'tipo_atencion' => 'FISICA',
                'id_regional' => $regLPZ ? $regLPZ->id : null,
                'id_unidad_organizacional' => $unidadGG ? $unidadGG->id : null,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]
        );

        $ventanillaDigital = Ventanilla::updateOrCreate(
            ['nombre' => 'Ventanilla Única Digital EMAPA'],
            [
                'direccion' => 'Portal Institucional https://emapa.gob.bo/ventanilla-digital',
                'tipo_atencion' => 'DIGITAL',
                'id_regional' => null,
                'id_unidad_organizacional' => $unidadGG ? $unidadGG->id : null,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]
        );

        // 2. OBTENER PERSONAS CLAVE
        $personas = Persona::all();
        if ($personas->count() < 3) return;

        $p1_gg = $personas[0]; // Franklin Flores - Gerente General
        $p2_gaf = $personas[1]; // Marcelo Choque - Gerente Admin
        $p3_rrhh = $personas[4] ?? $personas[2]; // Jefa RRHH
        $p4_com = $personas[2] ?? $personas[1]; // Comercialización
        $p5_silos = $personas[3] ?? $personas[1]; // Producción

        $puestoGG = Puesto::where('id_unidad_organizacional', $unidadGG ? $unidadGG->id : 1)->first();
        $puestoGAF = Puesto::where('id_unidad_organizacional', $unidadGAF ? $unidadGAF->id : 2)->first();
        $puestoRRHH = Puesto::where('id_unidad_organizacional', $unidadURRH ? $unidadURRH->id : 5)->first();

        // 3. GENERAR DOCUMENTO OFICIAL 1: INFORME TÉCNICO DE ADQUISICIÓN DE GRANOS
        $plantillaInf = PlantillaDocumento::where('sigla', 'INF')->first();
        $citeDoc1 = $citeService->generarCiteDocumento($unidadGAF ? $unidadGAF->id : 2, 'INF', 2026);
        $tokenDoc1 = $citeService->generarCodigoVerificacion();

        $doc1 = Documento::create([
            'cite' => $citeDoc1,
            'codigo_verificacion' => $tokenDoc1,
            'tipo_documento' => 'INFORME_TECNICO',
            'asunto' => 'INFORME TÉCNICO DE EVALUACIÓN PARA LA COMPRA ESTRATÉGICA DE TRIGO Y MAÍZ CAMPAÑA 2026',
            'contenido_html' => '<h3>1. ANTECEDENTES</h3><p>En el marco del plan nacional de seguridad y soberanía alimentaria, se realizó el levantamiento de stock en los silos San Pedro y Cuatro Cañadas.</p><h3>2. EVALUACIÓN FINANCIERA Y LOGÍSTICA</h3><p>Se cuenta con la disponibilidad presupuestaria para la adquisición de 50.000 toneladas métricas de grano de trigo a precio subvencionado de fomento al productor.</p><h3>3. CONCLUSIONES Y RECOMENDACIONES</h3><p>Se recomienda la suscripción inmediata de los contratos de acopio y derivación a la Gerencia General para visto bueno.</p>',
            'estado' => 'FIRMADO',
            'id_plantilla' => $plantillaInf ? $plantillaInf->id : null,
            'id_unidad_generadora' => $unidadGAF ? $unidadGAF->id : 2,
            'id_creador' => $p2_gaf->id,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(5),
        ]);

        // Participantes Doc 1
        $part1_rem = ParticipanteDocumento::create([
            'id_documento' => $doc1->id,
            'id_persona' => $p2_gaf->id,
            'id_puesto' => $puestoGAF ? $puestoGAF->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Gerente de Administración y Finanzas',
            'bandeja' => 'FIRMADOS',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(5),
        ]);

        $part1_dest = ParticipanteDocumento::create([
            'id_documento' => $doc1->id,
            'id_persona' => $p1_gg->id,
            'id_puesto' => $puestoGG ? $puestoGG->id : null,
            'tipo_participacion' => 'DESTINATARIO_A',
            'orden_participacion' => 2,
            'cargo_snapshot' => 'Gerente General Ejecutivo',
            'bandeja' => 'FIRMADOS',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(5),
        ]);

        // Firmas Doc 1
        $firmaService->firmarDocumento($doc1->id, $p2_gaf->id, '1234', 'PIN_ELECTRONICO');
        $firmaService->firmarDocumento($doc1->id, $p1_gg->id, '1234', 'PIN_ELECTRONICO');

        // 4. CREAR HOJA DE RUTA 1 VINCULADA AL INFORME
        $citeHR1 = $citeService->generarCiteHojaRuta($regLPZ ? $regLPZ->id : 1, 2026);
        $hr1 = HojaRuta::create([
            'nro_hoja_ruta' => $citeHR1,
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'origen' => 'INTERNO',
            'asunto' => 'SOLICITUD DE APROBACIÓN Y DESEMBOLSO PARA ACOPIO DE TRIGO GESTIÓN 2026',
            'referencia' => "Adjunta {$doc1->cite}",
            'prioridad' => 'URGENTE',
            'confidencial' => false,
            'nro_fojas' => 18,
            'nro_anexos' => 3,
            'id_unidad_origen' => $unidadGAF ? $unidadGAF->id : 2,
            'id_persona_origen' => $p2_gaf->id,
            'id_cargo_origen' => $puestoGAF ? $puestoGAF->id : null,
            'estado' => 'EN_PROCESO',
            'fecha_solicitud' => now()->subDays(4),
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(4),
        ]);

        $doc1->id_hoja_ruta = $hr1->id;
        $doc1->save();
        $hr1->documentos()->attach($doc1->id, ['es_documento_principal' => true]);

        // Derivación 1: GAF ➔ GG
        $der1 = Derivacion::create([
            'id_hoja_ruta' => $hr1->id,
            'id_documento_principal' => $doc1->id,
            'id_unidad_origen' => $unidadGAF ? $unidadGAF->id : 2,
            'id_funcionario_origen' => $p2_gaf->id,
            'id_cargo_origen' => $puestoGAF ? $puestoGAF->id : null,
            'id_unidad_destino' => $unidadGG ? $unidadGG->id : 1,
            'id_funcionario_destino' => $p1_gg->id,
            'id_cargo_destino' => $puestoGG ? $puestoGG->id : null,
            'proveido' => 'PASE A SUS EFECTOS',
            'instruccion_detalle' => 'Remito informe técnico para su respectiva consideración y autorización de desembolso.',
            'prioridad' => 'URGENTE',
            'dias_plazo' => 1,
            'fecha_limite' => now()->subDays(3)->toDateString(),
            'fecha_derivacion' => now()->subDays(4),
            'fecha_recepcion' => now()->subDays(4)->addHours(2),
            'fecha_atencion' => now()->subDays(3),
            'id_usuario_atencion' => 1,
            'estado_derivacion' => 'PROCESADO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(4),
        ]);
        $der1->mpath = (string)$der1->id;
        $der1->save();

        // Derivación 2 (Bifurcación / Salto): GG ➔ GCOM con proveído
        $der2 = Derivacion::create([
            'id_hoja_ruta' => $hr1->id,
            'id_derivacion_padre' => $der1->id,
            'mpath' => "{$der1->id}.2",
            'id_documento_principal' => $doc1->id,
            'id_unidad_origen' => $unidadGG ? $unidadGG->id : 1,
            'id_funcionario_origen' => $p1_gg->id,
            'id_cargo_origen' => $puestoGG ? $puestoGG->id : null,
            'id_unidad_destino' => $unidadGCOM ? $unidadGCOM->id : 3,
            'id_funcionario_destino' => $p4_com->id,
            'proveido' => 'PROCEDER SEGÚN REGLAMENTO',
            'instruccion_detalle' => 'Autorizado. Coordinar con los centros de acopio y proceder con la logística de recepción.',
            'prioridad' => 'ALTA',
            'dias_plazo' => 2,
            'fecha_limite' => now()->addDays(1)->toDateString(),
            'fecha_derivacion' => now()->subDays(1),
            'fecha_recepcion' => now()->subDays(1)->addHours(3),
            'estado_derivacion' => 'RECIBIDO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(1),
        ]);

        $hr1->esta_con = [
            'id_funcionario' => $p4_com->id,
            'funcionario' => $p4_com->nombre_completo,
            'id_unidad' => $unidadGCOM ? $unidadGCOM->id : 3,
            'unidad' => $unidadGCOM ? $unidadGCOM->nombre : 'Gerencia de Comercialización',
            'fecha_derivacion' => now()->subDays(1)->toDateTimeString(),
            'estado' => 'RECIBIDO',
        ];
        $hr1->save();

        // 5. CREAR HOJA DE RUTA 2: CORRESPONDENCIA EXTERNA DE MINISTERIO
        $citeHR2 = $citeService->generarCiteHojaRuta($regLPZ ? $regLPZ->id : 1, 2026);
        $hr2 = HojaRuta::create([
            'nro_hoja_ruta' => $citeHR2,
            'gestion' => 2026,
            'tipo_hr' => 'EXTERNA',
            'origen' => 'VENTANILLA_FISICA',
            'asunto' => 'SOLICITUD DE INFORME DE ABASTECIMIENTO DE HARINA DE TRIGO A PANIFICADORES',
            'referencia' => 'NOTA CITE: MDPyEP/DESP/045/2026',
            'prioridad' => 'ALTA',
            'confidencial' => false,
            'nro_fojas' => 5,
            'nro_anexos' => 1,
            'remitente_externo' => 'MINISTERIO DE DESARROLLO PRODUCTIVO Y ECONOMÍA PLURAL',
            'id_ventanilla_origen' => $ventanillaCentral->id,
            'estado' => 'EN_PROCESO',
            'fecha_solicitud' => now()->subDays(2),
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(2),
        ]);

        // Derivación Ventanilla ➔ Gerencia General
        $workflowService->derivar([
            'id_hoja_ruta' => $hr2->id,
            'id_unidad_origen' => $unidadGG ? $unidadGG->id : 1,
            'id_funcionario_origen' => $p1_gg->id,
            'id_cargo_origen' => $puestoGG ? $puestoGG->id : null,
            'proveido' => 'PARA INFORME TÉCNICO CIRCUNSTANCIADO',
            'instruccion_detalle' => 'Preparar reporte detallado de cupos asignados por federación panificadora.',
            'dias_plazo' => 2,
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidadGCOM ? $unidadGCOM->id : 3,
                    'id_funcionario_destino' => $p4_com->id,
                    'es_copia' => false,
                ]
            ],
        ]);

        // 6. CREAR DOCUMENTO 2: MEMORÁNDUM DE DESIGNACIÓN INTERNA
        $plantillaMem = PlantillaDocumento::where('sigla', 'MEM')->first();
        $citeDoc2 = $citeService->generarCiteDocumento($unidadURRH ? $unidadURRH->id : 5, 'MEM', 2026);
        $tokenDoc2 = $citeService->generarCodigoVerificacion();

        $doc2 = Documento::create([
            'cite' => $citeDoc2,
            'codigo_verificacion' => $tokenDoc2,
            'tipo_documento' => 'MEMORANDUM',
            'asunto' => 'DESIGNACIÓN DE COMISIÓN DE RECEPCIÓN Y VERIFICACIÓN EN PLANTA DE SILOS',
            'contenido_html' => '<p>Por medio del presente Memorándum, se designa a su persona como responsable de la comisión técnica de verificación física y toma de muestras de laboratorio en los silos de almacenamiento.</p><p>Debiendo emitir el correspondiente informe de conformidad en un plazo no mayor a 48 horas.</p>',
            'estado' => 'FIRMADO',
            'id_plantilla' => $plantillaMem ? $plantillaMem->id : null,
            'id_unidad_generadora' => $unidadURRH ? $unidadURRH->id : 5,
            'id_creador' => $p3_rrhh->id,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDay(),
        ]);

        $part2_rem = ParticipanteDocumento::create([
            'id_documento' => $doc2->id,
            'id_persona' => $p3_rrhh->id,
            'id_puesto' => $puestoRRHH ? $puestoRRHH->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Jefe de Recursos Humanos',
            'bandeja' => 'FIRMADOS',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDay(),
        ]);

        $firmaService->firmarDocumento($doc2->id, $p3_rrhh->id, '1234', 'PIN_ELECTRONICO');
    }
}
