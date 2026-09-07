<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use App\Models\Correspondencia\AccesoCompartido;
use App\Models\Correspondencia\AgrupacionHojaRuta;
use App\Models\Correspondencia\ArchivoAdjunto;
use App\Models\Correspondencia\Correlativo;
use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\DespachoSalida;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\Etiqueta;
use App\Models\Correspondencia\EtiquetaParticipante;
use App\Models\Correspondencia\FirmaAprobacion;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Correspondencia\PermisoDerivacion;
use App\Models\Correspondencia\PlantillaDocumento;
use App\Models\Correspondencia\SolicitudCiudadana;
use App\Models\Correspondencia\UsuarioSecretario;
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
        $citeService = new CiteGeneratorService;
        $workflowService = new DerivacionWorkflowService;
        $firmaService = new FirmaDigitalService($citeService);

        // =========================================================================
        // LIMPIEZA PREVIA PARA IDEMPOTENCIA
        // =========================================================================
        ArchivoAdjunto::query()->delete();
        FirmaAprobacion::query()->delete();
        ParticipanteDocumento::query()->delete();
        DB::table('correspondencia.hoja_ruta_documentos')->delete();
        Documento::query()->delete();
        Derivacion::query()->delete();
        AgrupacionHojaRuta::query()->delete();
        HojaRuta::query()->delete();
        DespachoSalida::query()->delete();
        SolicitudCiudadana::query()->delete();
        EtiquetaParticipante::query()->delete();
        Etiqueta::query()->delete();
        AccesoCompartido::query()->delete();

        // =========================================================================
        // 1. REGIONALES Y UNIDADES ORGANIZACIONALES
        // =========================================================================
        $regLPZ = Regional::where('sigla', 'LPZ')->first() ?: Regional::first();
        $regSCZ = Regional::where('sigla', 'SCZ')->first() ?: Regional::first();

        $unidadGG = UnidadOrganizacional::where('sigla', 'GG')->first() ?: UnidadOrganizacional::first();
        $unidadGAF = UnidadOrganizacional::where('sigla', 'GAF')->first() ?: UnidadOrganizacional::first();
        $unidadURRH = UnidadOrganizacional::where('sigla', 'URRH')->first() ?: UnidadOrganizacional::first();
        $unidadGCOM = UnidadOrganizacional::where('sigla', 'GCOM')->first() ?: UnidadOrganizacional::first();
        $unidadGPROD = UnidadOrganizacional::where('sigla', 'GPROD')->first() ?: UnidadOrganizacional::first();
        $unidadUAJ = UnidadOrganizacional::where('sigla', 'UAJ')->first() ?: UnidadOrganizacional::first();

        // =========================================================================
        // 2. CORRELATIVOS OFICIALES GESTIÓN 2026
        // =========================================================================
        $unidades = UnidadOrganizacional::where('_estado', 'ACTIVO')->get();
        foreach ($unidades as $u) {
            foreach (['MEM', 'INF', 'NI', 'CIR', 'CAR', 'RES'] as $tipo) {
                Correlativo::updateOrCreate(
                    [
                        'gestion' => 2026,
                        'tipo_correlativo' => 'DOCUMENTO',
                        'sigla_plantilla' => $tipo,
                        'id_unidad_organizacional' => $u->id,
                        'sigla_regional' => 'LPZ',
                    ],
                    [
                        'correlativo_actual' => rand(5, 25),
                        'formato_cite' => 'EMAPA/{REG}/{UNIDAD}/{TIPO}/{NRO}/{GESTION}',
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CREAR',
                        '_usuario_creacion' => 1,
                        '_fecha_creacion' => now(),
                    ]
                );
            }
        }

        // =========================================================================
        // 3. VENTANILLAS ÚNICAS DE CORRESPONDENCIA
        // =========================================================================
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

        $ventanillaSCZ = Ventanilla::updateOrCreate(
            ['nombre' => 'Ventanilla Regional Santa Cruz'],
            [
                'direccion' => 'Av. San Martín 4to Anillo, Edif. Torre Empresarial Piso 3',
                'tipo_atencion' => 'FISICA',
                'id_regional' => $regSCZ ? $regSCZ->id : null,
                'id_unidad_organizacional' => $unidadGCOM ? $unidadGCOM->id : null,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]
        );

        $ventanillaDigital = Ventanilla::updateOrCreate(
            ['nombre' => 'Ventanilla Única Digital EMAPA (Portal Ciudadano)'],
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

        // =========================================================================
        // 4. PERSONAS Y PUESTOS CLAVE
        // =========================================================================
        $personas = Persona::orderBy('id', 'asc')->get();
        if ($personas->count() < 4) {
            return;
        }

        $p1_gg = Persona::find(1) ?: $personas->first();
        $p2_gaf = Persona::find(2) ?: $personas->get(1);
        $p3_rrhh = Persona::find(4) ?: $personas->get(2);
        $p4_com = Persona::find(3) ?: $personas->get(3);
        $p5_prod = Persona::find(5) ?: $personas->get(4);

        User::where('id', 1)->update(['usr_externo_id' => $p1_gg->id]);
        User::where('usr_usuario', 'admin')->update(['usr_externo_id' => $p1_gg->id]);
        User::where('usr_usuario', 'gerente.general')->update(['usr_externo_id' => $p1_gg->id]);
        User::where('usr_usuario', 'gerente.gaf')->update(['usr_externo_id' => $p2_gaf->id]);
        User::where('usr_usuario', 'jefe.rrhh')->update(['usr_externo_id' => $p3_rrhh->id]);
        User::where('usr_usuario', 'gerente.comercial')->update(['usr_externo_id' => $p4_com->id]);

        $puestoGG = Puesto::where('id_unidad_organizacional', $unidadGG ? $unidadGG->id : 1)->first();
        $puestoGAF = Puesto::where('id_unidad_organizacional', $unidadGAF ? $unidadGAF->id : 2)->first();
        $puestoRRHH = Puesto::where('id_unidad_organizacional', $unidadURRH ? $unidadURRH->id : 5)->first();
        $puestoCOM = Puesto::where('id_unidad_organizacional', $unidadGCOM ? $unidadGCOM->id : 3)->first();
        $puestoPROD = Puesto::where('id_unidad_organizacional', $unidadGPROD ? $unidadGPROD->id : 4)->first();

        // =========================================================================
        // 5. DOCUMENTOS OFICIALES Y FIRMAS SECUENCIALES (VIA -> DE)
        // =========================================================================
        $plantillaInf = PlantillaDocumento::where('sigla', 'INF')->first();
        $plantillaMem = PlantillaDocumento::where('sigla', 'MEM')->first();
        $plantillaNi = PlantillaDocumento::where('sigla', 'NI')->first();
        $plantillaCir = PlantillaDocumento::where('sigla', 'CIR')->first();

        // Documento 1: Informe Técnico (se firmará secuencialmente VIA -> DE)
        $doc1 = Documento::create([
            'cite' => $citeService->generarCiteDocumento($unidadGAF ? $unidadGAF->id : 2, 'INF', 2026),
            'codigo_verificacion' => $citeService->generarCodigoVerificacion(),
            'tipo_documento' => 'INFORME_TECNICO',
            'asunto' => 'INFORME TÉCNICO DE EVALUACIÓN PARA LA COMPRA ESTRATÉGICA DE TRIGO Y MAÍZ CAMPAÑA 2026',
            'contenido_html' => '<h3>1. ANTECEDENTES</h3><p>En el marco del plan nacional de seguridad y soberanía alimentaria, se realizó el levantamiento de stock en los silos San Pedro y Cuatro Cañadas.</p><h3>2. EVALUACIÓN FINANCIERA</h3><p>Se cuenta con la disponibilidad presupuestaria para la adquisición de 50.000 toneladas métricas de grano a precio de fomento al productor.</p><h3>3. CONCLUSIONES Y RECOMENDACIONES</h3><p>Se recomienda la suscripción de contratos de acopio inmediato y remisión a la Gerencia General.</p>',
            'estado' => 'EN_REVISION',
            'id_plantilla' => $plantillaInf ? $plantillaInf->id : null,
            'id_unidad_generadora' => $unidadGAF ? $unidadGAF->id : 2,
            'id_creador' => $p2_gaf->id,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(6),
        ]);

        ParticipanteDocumento::create([
            'id_documento' => $doc1->id,
            'id_persona' => $p2_gaf->id,
            'id_puesto' => $puestoGAF ? $puestoGAF->id : null,
            'tipo_participacion' => 'VIA',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Gerente de Administración y Finanzas',
            'bandeja' => 'FIRMADOS',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(6),
        ]);

        ParticipanteDocumento::create([
            'id_documento' => $doc1->id,
            'id_persona' => $p1_gg->id,
            'id_puesto' => $puestoGG ? $puestoGG->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 2,
            'cargo_snapshot' => 'Gerente General Ejecutivo',
            'bandeja' => 'FIRMADOS',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(6),
        ]);

        $firmaService->firmarDocumento($doc1->id, $p2_gaf->id, '1234', 'PIN_ELECTRONICO');
        $firmaService->firmarDocumento($doc1->id, $p1_gg->id, '1234', 'TOKEN_DIGITAL');

        // Documento 2: Memorándum EN REVISIÓN
        $doc2 = Documento::create([
            'cite' => $citeService->generarCiteDocumento($unidadURRH ? $unidadURRH->id : 5, 'MEM', 2026),
            'codigo_verificacion' => $citeService->generarCodigoVerificacion(),
            'tipo_documento' => 'MEMORANDUM',
            'asunto' => 'DESIGNACIÓN DE COMISIÓN DE RECEPCIÓN Y VERIFICACIÓN EN PLANTA DE SILOS',
            'contenido_html' => '<p>Por medio del presente Memorándum, se designa a su persona como responsable de la comisión técnica de verificación física y toma de muestras en los silos de almacenamiento.</p>',
            'estado' => 'EN_REVISION',
            'id_plantilla' => $plantillaMem ? $plantillaMem->id : null,
            'id_unidad_generadora' => $unidadURRH ? $unidadURRH->id : 5,
            'id_creador' => $p3_rrhh->id,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(2),
        ]);

        ParticipanteDocumento::create([
            'id_documento' => $doc2->id,
            'id_persona' => $p3_rrhh->id,
            'id_puesto' => $puestoRRHH ? $puestoRRHH->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Jefe de Recursos Humanos',
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(2),
        ]);

        // Documento 3: Nota Interna OBSERVADA
        $doc3 = Documento::create([
            'cite' => $citeService->generarCiteDocumento($unidadGCOM ? $unidadGCOM->id : 3, 'NI', 2026),
            'codigo_verificacion' => $citeService->generarCodigoVerificacion(),
            'tipo_documento' => 'NOTA_INTERNA',
            'asunto' => 'SOLICITUD DE TRANSFERENCIA PRESUPUESTARIA PARA FERIAS DEL PRODUCTOR AL CONSUMIDOR',
            'contenido_html' => '<p>Se solicita incremento de partida presupuestaria para el despliegue logístico de camiones distribuidores.</p>',
            'estado' => 'OBSERVADO',
            'motivo_anulacion' => 'Observación de revisión: Falta adjuntar cuadro comparativo de costos por ruta y cotizaciones.',
            'id_plantilla' => $plantillaNi ? $plantillaNi->id : null,
            'id_unidad_generadora' => $unidadGCOM ? $unidadGCOM->id : 3,
            'id_creador' => $p4_com->id,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(3),
        ]);

        ParticipanteDocumento::create([
            'id_documento' => $doc3->id,
            'id_persona' => $p4_com->id,
            'id_puesto' => $puestoCOM ? $puestoCOM->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Gerente de Comercialización',
            'bandeja' => 'OBSERVADOS',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(3),
        ]);

        // Documento 4: Circular BORRADOR
        $doc4 = Documento::create([
            'cite' => $citeService->generarCiteDocumento($unidadGG ? $unidadGG->id : 1, 'CIR', 2026),
            'codigo_verificacion' => $citeService->generarCodigoVerificacion(),
            'tipo_documento' => 'CIRCULAR',
            'asunto' => 'DISPOSICIÓN DE HORARIO CONTINUO POR JORNADA NACIONAL DE VACUNACIÓN',
            'contenido_html' => '<p>Se comunica a todo el personal dependiente de EMAPA que el día viernes se aplicará horario continuo de 08:00 a 16:00.</p>',
            'estado' => 'BORRADOR',
            'id_plantilla' => $plantillaCir ? $plantillaCir->id : null,
            'id_unidad_generadora' => $unidadGG ? $unidadGG->id : 1,
            'id_creador' => $p1_gg->id,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHour(),
        ]);

        // Documento 5: PENDIENTE DE FIRMA PARA EL USUARIO 1 (GG) como REMITENTE_DE
        $doc5 = Documento::create([
            'cite' => $citeService->generarCiteDocumento($unidadGG ? $unidadGG->id : 1, 'MEM', 2026),
            'codigo_verificacion' => $citeService->generarCodigoVerificacion(),
            'tipo_documento' => 'MEMORANDUM',
            'asunto' => 'INSTRUCCIÓN PARA RELEVAMIENTO DE INVENTARIO FÍSICO EN SILOS SAN PEDRO Y CUATRO CAÑADAS',
            'contenido_html' => '<p>Se instruye con carácter de urgencia a las comisiones técnicas constituirse en las plantas de almacenamiento para proceder con el inventario físico valorado de existencias de granos.</p>',
            'estado' => 'EN_REVISION',
            'id_plantilla' => $plantillaMem ? $plantillaMem->id : null,
            'id_unidad_generadora' => $unidadGG ? $unidadGG->id : 1,
            'id_creador' => $p1_gg->id,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(4),
        ]);

        ParticipanteDocumento::create([
            'id_documento' => $doc5->id,
            'id_persona' => $p1_gg->id,
            'id_puesto' => $puestoGG ? $puestoGG->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Gerente General Ejecutivo',
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(4),
        ]);

        // Documento 6: PENDIENTE DE VISTO BUENO (VIA) PARA EL USUARIO 1 (GG)
        $doc6 = Documento::create([
            'cite' => $citeService->generarCiteDocumento($unidadGAF ? $unidadGAF->id : 2, 'INF', 2026),
            'codigo_verificacion' => $citeService->generarCodigoVerificacion(),
            'tipo_documento' => 'INFORME_TECNICO',
            'asunto' => 'REQUERIMIENTO DE ADQUISICIÓN DE SILOBOLSAS Y MANTAS TÉRMICAS PARA ACOPIO DE ARROZ 2026',
            'contenido_html' => '<p>Se remite el informe técnico y especificaciones para la compra de 500 silobolsas de alta densidad para resguardo de la cosecha de arroz en Santa Cruz.</p>',
            'estado' => 'EN_REVISION',
            'id_plantilla' => $plantillaInf ? $plantillaInf->id : null,
            'id_unidad_generadora' => $unidadGAF ? $unidadGAF->id : 2,
            'id_creador' => $p2_gaf->id,
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(6),
        ]);

        // Revisor VIA (GG / Usuario 1)
        ParticipanteDocumento::create([
            'id_documento' => $doc6->id,
            'id_persona' => $p1_gg->id,
            'id_puesto' => $puestoGG ? $puestoGG->id : null,
            'tipo_participacion' => 'VIA',
            'orden_participacion' => 1,
            'cargo_snapshot' => 'Gerente General Ejecutivo',
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(6),
        ]);

        // Emisor DE (GAF)
        ParticipanteDocumento::create([
            'id_documento' => $doc6->id,
            'id_persona' => $p2_gaf->id,
            'id_puesto' => $puestoGAF ? $puestoGAF->id : null,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 2,
            'cargo_snapshot' => 'Gerente de Administración y Finanzas',
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(6),
        ]);

        // Adjuntos
        ArchivoAdjunto::create([
            'id_documento' => $doc1->id,
            'nombre_original' => 'Cuadro_Financiero_Acopio_Trigo_2026.xlsx',
            'ruta_almacenamiento' => 'correspondencia/adjuntos/cuadro_trigo.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'tamanio_bytes' => 145200,
            'hash_sha256' => hash('sha256', 'Cuadro_Financiero_Acopio_Trigo_2026_'.time()),
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(6),
        ]);

        // =========================================================================
        // 6. HOJAS DE RUTA (EN PROCESO, AGRUPADA, CERRADA, EN TRÁNSITO)
        // =========================================================================

        // HR 1: Adquisición de Trigo (EN PROCESO con derivación múltiple y árbol mpath)
        $hr1 = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta($regLPZ ? $regLPZ->id : 1, 2026),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'origen' => 'INTERNO',
            'asunto' => 'SOLICITUD DE APROBACIÓN Y DESEMBOLSO PARA ACOPIO DE TRIGO GESTIÓN 2026',
            'referencia' => "Adjunta CITE {$doc1->cite}",
            'prioridad' => 'URGENTE',
            'confidencial' => false,
            'nro_fojas' => 25,
            'nro_anexos' => 2,
            'id_unidad_origen' => $unidadGAF ? $unidadGAF->id : 2,
            'id_persona_origen' => $p2_gaf->id,
            'id_cargo_origen' => $puestoGAF ? $puestoGAF->id : null,
            'estado' => 'EN_PROCESO',
            'fecha_solicitud' => now()->subDays(5),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(5),
        ]);
        $doc1->id_hoja_ruta = $hr1->id;
        $doc1->save();
        $hr1->documentos()->attach($doc1->id, ['es_documento_principal' => true, '_estado' => 'ACTIVO', '_usuario_creacion' => 1, '_fecha_creacion' => now()]);

        // Derivación Raíz 1: GAF ➔ GG (PROCESADO)
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
            'fecha_limite' => now()->subDays(4)->toDateString(),
            'fecha_derivacion' => now()->subDays(5),
            'fecha_recepcion' => now()->subDays(5)->addHours(2),
            'fecha_atencion' => now()->subDays(4),
            'id_usuario_atencion' => 1,
            'estado_derivacion' => 'PROCESADO',
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(5),
        ]);
        $der1->mpath = (string) $der1->id;
        $der1->save();

        // Derivación Hija 2: GG ➔ GCOM (RECIBIDO) + Copia CC a GPROD
        $der2_principal = Derivacion::create([
            'id_hoja_ruta' => $hr1->id,
            'id_derivacion_padre' => $der1->id,
            'mpath' => "{$der1->id}.2",
            'id_documento_principal' => $doc1->id,
            'id_unidad_origen' => $unidadGG ? $unidadGG->id : 1,
            'id_funcionario_origen' => $p1_gg->id,
            'id_cargo_origen' => $puestoGG ? $puestoGG->id : null,
            'id_unidad_destino' => $unidadGCOM ? $unidadGCOM->id : 3,
            'id_funcionario_destino' => $p4_com->id,
            'id_cargo_destino' => $puestoCOM ? $puestoCOM->id : null,
            'proveido' => 'PROCEDER SEGÚN REGLAMENTO',
            'instruccion_detalle' => 'Autorizado. Coordinar con centros de acopio y centros de distribución.',
            'prioridad' => 'ALTA',
            'dias_plazo' => 2,
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'fecha_derivacion' => now()->subDays(1),
            'fecha_recepcion' => now()->subDays(1)->addHours(1),
            'estado_derivacion' => 'RECIBIDO',
            'es_copia' => false,
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(1),
        ]);

        $der2_copia = Derivacion::create([
            'id_hoja_ruta' => $hr1->id,
            'id_derivacion_padre' => $der1->id,
            'mpath' => "{$der1->id}.3",
            'id_documento_principal' => $doc1->id,
            'id_unidad_origen' => $unidadGG ? $unidadGG->id : 1,
            'id_funcionario_origen' => $p1_gg->id,
            'id_cargo_origen' => $puestoGG ? $puestoGG->id : null,
            'id_unidad_destino' => $unidadGPROD ? $unidadGPROD->id : 4,
            'id_funcionario_destino' => $p5_prod->id,
            'id_cargo_destino' => $puestoPROD ? $puestoPROD->id : null,
            'proveido' => 'PARA SU CONOCIMIENTO',
            'instruccion_detalle' => 'Copia para alistar tolvas y balanzas de pesaje en silos.',
            'prioridad' => 'MEDIA',
            'dias_plazo' => 3,
            'fecha_limite' => now()->addDays(3)->toDateString(),
            'fecha_derivacion' => now()->subDays(1),
            'fecha_recepcion' => now()->subDays(1)->addHours(4),
            'estado_derivacion' => 'RECIBIDO',
            'es_copia' => true,
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(1),
        ]);

        $hr1->esta_con = [
            'id_funcionario' => $p4_com->id,
            'funcionario' => $p4_com->nombre_completo,
            'id_unidad' => $unidadGCOM ? $unidadGCOM->id : 3,
            'unidad' => $unidadGCOM ? $unidadGCOM->nombre : 'Gerencia de Comercialización',
            'fecha_derivacion' => now()->subDays(1)->toDateTimeString(),
            'fecha_recepcion' => now()->subDays(1)->addHours(1)->toDateTimeString(),
            'estado' => 'RECIBIDO',
        ];
        $hr1->save();

        // HR 2: Entrada Externa de Ministerio (EN TRÁNSITO / PENDIENTE RECEPCIÓN)
        $hr2 = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta($regLPZ ? $regLPZ->id : 1, 2026),
            'gestion' => 2026,
            'tipo_hr' => 'EXTERNA',
            'origen' => 'VENTANILLA_FISICA',
            'asunto' => 'SOLICITUD DE INFORME DE ABASTECIMIENTO DE HARINA DE TRIGO A PANIFICADORES DE EL ALTO',
            'referencia' => 'NOTA MDPyEP/DESP/089/2026',
            'prioridad' => 'ALTA',
            'confidencial' => false,
            'nro_fojas' => 8,
            'nro_anexos' => 1,
            'remitente_externo' => 'MINISTERIO DE DESARROLLO PRODUCTIVO Y ECONOMÍA PLURAL',
            'id_ventanilla_origen' => $ventanillaCentral->id,
            'estado' => 'EN_PROCESO',
            'fecha_solicitud' => now()->subHours(8),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(8),
        ]);

        $workflowService->derivar([
            'id_hoja_ruta' => $hr2->id,
            'id_unidad_origen' => $unidadGG ? $unidadGG->id : 1,
            'id_funcionario_origen' => $p1_gg->id,
            'id_cargo_origen' => $puestoGG ? $puestoGG->id : null,
            'proveido' => 'PARA INFORME TÉCNICO CIRCUNSTANCIADO',
            'instruccion_detalle' => 'Preparar informe con volumen entregado en el mes de febrero.',
            'dias_plazo' => 2,
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidadGCOM ? $unidadGCOM->id : 3,
                    'id_funcionario_destino' => $p4_com->id,
                    'es_copia' => false,
                ],
            ],
        ]);

        // HR 3: Expediente CONCLUIDO Y ARCHIVADO
        $hr3 = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta($regSCZ ? $regSCZ->id : 2, 2026),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'origen' => 'INTERNO',
            'asunto' => 'MANTENIMIENTO PREVENTIVO DE GENERADORES ELÉCTRICOS EN PLANTA SAN PEDRO',
            'referencia' => 'PLANTA SAN PEDRO - ORDEN DE TRABAJO 44/2026',
            'prioridad' => 'MEDIA',
            'confidencial' => false,
            'nro_fojas' => 12,
            'nro_anexos' => 0,
            'id_unidad_origen' => $unidadGPROD ? $unidadGPROD->id : 4,
            'id_persona_origen' => $p5_prod->id,
            'id_cargo_origen' => $puestoPROD ? $puestoPROD->id : null,
            'estado' => 'CERRADO',
            'motivo_cierre' => 'Trabajos de mantenimiento ejecutados a satisfacción con acta de recepción definitiva.',
            'fecha_cierre' => now()->subDay(),
            'id_usuario_cierre' => 1,
            'fecha_solicitud' => now()->subDays(10),
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(10),
        ]);

        // HR 4: Expediente AGRUPADO (Anexado a HR 1)
        $hr4_anexada = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta($regLPZ ? $regLPZ->id : 1, 2026),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'origen' => 'INTERNO',
            'asunto' => 'ANTECEDENTES TÉCNICOS Y ANÁLISIS DE LABORATORIO DE CALIDAD DE GRANO',
            'referencia' => 'CERTIFICADOS DE CALIDAD SENASAG 2026',
            'prioridad' => 'MEDIA',
            'confidencial' => false,
            'nro_fojas' => 30,
            'nro_anexos' => 4,
            'id_unidad_origen' => $unidadGPROD ? $unidadGPROD->id : 4,
            'id_persona_origen' => $p5_prod->id,
            'estado' => 'AGRUPADO',
            'fecha_solicitud' => now()->subDays(7),
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(7),
        ]);

        $workflowService->agrupar(
            $hr1->id,
            $hr4_anexada->id,
            $p2_gaf->id,
            'Se anexa el expediente técnico de análisis bromatológico como respaldo indispensable para la compra.'
        );

        // =========================================================================
        // 7. DESPACHOS DE SALIDA EXTERNA
        // =========================================================================
        DespachoSalida::create([
            'id_hoja_ruta' => $hr3->id,
            'tipo_despacho' => 'COURIER_POSTAL',
            'destinatario_institucion' => 'EMPRESA NACIONAL DE ELECTRICIDAD (ENDE)',
            'destinatario_persona' => 'ING. ROBERTO VACA - SUPERVISOR REGIONAL',
            'destinatario_direccion' => 'Av. Ballivián Nro. 500 Santa Cruz de la Sierra',
            'destinatario_ciudad' => 'Santa Cruz',
            'empresa_courier' => 'AGENCIA BOLIVIANA DE CORREOS',
            'nro_guia_despacho' => 'GUIA-AGBC-SCZ-88412',
            'estado_despacho' => 'ENTREGADO_CON_ACUSE',
            'fecha_despacho' => now()->subDays(2),
            'fecha_entrega' => now()->subDay(),
            'observaciones' => 'Entregado con firma y sello en recepción de correspondencia.',
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(2),
        ]);

        DespachoSalida::create([
            'id_hoja_ruta' => $hr1->id,
            'tipo_despacho' => 'MENSAJERIA_INTERNA',
            'destinatario_institucion' => 'BANCO UNIÓN S.A. - BANCA INSTITUCIONAL',
            'destinatario_persona' => 'LIC. PATRICIA GUZMÁN',
            'destinatario_direccion' => 'Calle Mercado esq. Socabaya La Paz',
            'destinatario_ciudad' => 'La Paz',
            'nro_guia_despacho' => 'MENSAJ-INT-0441',
            'estado_despacho' => 'PENDIENTE_DESPACHO',
            'fecha_despacho' => now()->subHours(3),
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(3),
        ]);

        // =========================================================================
        // 8. SOLICITUDES CIUDADANAS (PORTAL WEB)
        // =========================================================================
        SolicitudCiudadana::create([
            'codigo_solicitud' => 'SOL-'.strtoupper(Str::random(8)),
            'solicitante_nombre' => 'ASOCIACIÓN DE PRODUCTORES DE ARROZ DE MONTERO',
            'solicitante_ci_nit' => '102938475',
            'solicitante_telefono' => '77098214',
            'solicitante_correo' => 'asociacion.montero@gmail.com',
            'tipo_solicitud' => 'SOLICITUD DE COMPRA Y ACOPIO',
            'descripcion_solicitud' => 'Presentamos solicitud formal para la venta de 5.000 quintales de arroz en chala con destino a la reserva estratégica nacional.',
            'estado_solicitud' => 'REGISTRADA',
            'fecha_solicitud' => now()->subHours(5),
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subHours(5),
        ]);

        SolicitudCiudadana::create([
            'codigo_solicitud' => 'SOL-'.strtoupper(Str::random(8)),
            'solicitante_nombre' => 'FEDERACIÓN DE PANIFICADORES ARTESANALES DE COCHABAMBA',
            'solicitante_ci_nit' => '4928172',
            'solicitante_telefono' => '72239401',
            'solicitante_correo' => 'panificadores.cba@hotmail.com',
            'tipo_solicitud' => 'AMPLIACIÓN DE CUPO DE HARINA',
            'descripcion_solicitud' => 'Solicitamos incremento del cupo mensual de harina subvencionada a 4 quintales diarios por panadería afiliada.',
            'estado_solicitud' => 'HOJA_RUTA_GENERADA',
            'id_hoja_ruta_generada' => $hr2->id,
            'fecha_solicitud' => now()->subDays(2),
            'fecha_atencion' => now()->subDays(1),
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now()->subDays(2),
        ]);

        // =========================================================================
        // 9. ETIQUETAS Y CARPETAS VIRTUALES
        // =========================================================================
        $et1 = Etiqueta::create([
            'nombre' => 'COMPRAS ESTRATÉGICAS 2026',
            'color' => '#1565C0',
            'id_usuario' => 1,
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        $et2 = Etiqueta::create([
            'nombre' => 'AUDITORÍA Y CONTROL SENASAG',
            'color' => '#6A1B9A',
            'id_usuario' => 1,
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        $et3 = Etiqueta::create([
            'nombre' => 'URGENTES Y PLAZOS CRÍTICOS',
            'color' => '#C62828',
            'id_usuario' => 1,
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        EtiquetaParticipante::create([
            'id_etiqueta' => $et1->id,
            'id_hoja_ruta' => $hr1->id,
            'id_usuario' => 1,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        EtiquetaParticipante::create([
            'id_etiqueta' => $et3->id,
            'id_hoja_ruta' => $hr1->id,
            'id_usuario' => 1,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        EtiquetaParticipante::create([
            'id_etiqueta' => $et2->id,
            'id_hoja_ruta' => $hr4_anexada->id,
            'id_usuario' => 1,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        // =========================================================================
        // 10. ACCESOS COMPARTIDOS
        // =========================================================================
        AccesoCompartido::create([
            'id_hoja_ruta' => $hr1->id,
            'id_usuario_autorizador' => 1,
            'id_usuario_destinatario' => 1,
            'fecha_expiracion' => now()->addDays(30),
            'motivo' => 'Acceso compartido temporal para auditoría y seguimiento técnico de acopio.',
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        // =========================================================================
        // 11. MATRIZ DE PERMISOS DE DERIVACIÓN INTER-UNIDADES
        // =========================================================================
        $paresPermisos = [
            [$unidadGG->id, $unidadGAF->id],
            [$unidadGG->id, $unidadGCOM->id],
            [$unidadGG->id, $unidadGPROD->id],
            [$unidadGAF->id, $unidadGG->id],
            [$unidadGAF->id, $unidadURRH->id],
            [$unidadGCOM->id, $unidadGG->id],
            [$unidadGCOM->id, $unidadGAF->id],
            [$unidadGPROD->id, $unidadGG->id],
            [$unidadGPROD->id, $unidadGAF->id],
        ];

        foreach ($paresPermisos as $par) {
            PermisoDerivacion::updateOrCreate(
                [
                    'id_origen' => $par[0],
                    'tipo_origen' => 'UNIDAD',
                    'id_destino' => $par[1],
                    'tipo_destino' => 'UNIDAD',
                ],
                [
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                    '_fecha_creacion' => now(),
                ]
            );
        }

        // =========================================================================
        // 12. SECRETARIOS / ASISTENTES EJECUTIVOS DELEGADOS
        // =========================================================================
        UsuarioSecretario::updateOrCreate(
            [
                'id_usuario' => 1,
                'id_puesto_titular' => $puestoGG ? $puestoGG->id : 1,
            ],
            [
                'permisos' => [
                    'ver_bandeja' => true,
                    'derivar' => true,
                    'recibir' => true,
                    'redactar' => true,
                    'anular' => false,
                ],
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => 1,
                '_fecha_creacion' => now(),
            ]
        );
    }
}
