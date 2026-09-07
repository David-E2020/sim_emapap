<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Correspondencia\ArchivoAdjunto;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\Etiqueta;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Correspondencia\Ventanilla;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use App\Services\Correspondencia\FirmaDigitalService;
use Database\Seeders\Correspondencia\CorrespondenciaDemoDataSeeder;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Tests\TestCase;

class CorrespondenciaExhaustiveTestSuiteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ejecutar seeder si no hay registros para garantizar un ambiente con datos institucionalmente válidos
        if (HojaRuta::count() === 0) {
            $this->seed(CorrespondenciaDemoDataSeeder::class);
        }
    }

    // =========================================================================
    // SUBMÓDULO 1: HOJAS DE RUTA Y GENERACIÓN DE CITES
    // =========================================================================
    public function test_submodulo_1_hojas_de_ruta_creacion_y_cites_validos(): void
    {
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();
        $puesto = Puesto::first();
        $regional = Regional::first();

        // 1. Crear Hoja de Ruta Interna
        $payload = [
            'tipo_hr' => 'INTERNA',
            'asunto' => 'AUDITORÍA INTEGRAL DE OPERACIONES SILO SAN PEDRO 2026',
            'referencia' => 'MEM-EMAPA-GG-012/2026',
            'prioridad' => 'URGENTE',
            'nro_fojas' => 35,
            'nro_anexos' => 4,
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'id_cargo_origen' => $puesto->id,
            'id_regional' => $regional ? $regional->id : 1,
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'id_cargo_destino' => $puesto->id,
                    'proveido' => 'PARA SU ATENCIÓN INMEDIATA',
                    'instruccion_detalle' => 'Iniciar relevamiento de inventario de granos.',
                    'dias_plazo' => 2,
                    'es_copia' => false,
                ],
            ],
        ];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/hojas-ruta', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $hrData = $response->json('data');

        $this->assertNotEmpty($hrData['nro_hoja_ruta']);
        $this->assertStringContainsString('HR-EMAPA-', $hrData['nro_hoja_ruta']);
        $this->assertEquals('EN_PROCESO', $hrData['estado']);
    }

    // =========================================================================
    // SUBMÓDULO 2: DERIVACIONES, ANULACIÓN EN TRÁNSITO Y DEVOLUCIÓN OBSERVADA
    // =========================================================================
    public function test_submodulo_2_flujo_completo_derivacion_devolucion_y_anulacion(): void
    {
        $personas = Persona::take(2)->get();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        $workflowService = new DerivacionWorkflowService;
        $citeService = new CiteGeneratorService;

        $hr = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(1, 2026),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'asunto' => 'EVALUACIÓN DE PERSONAL CONTRATO EVENTUAL',
            'estado' => 'EN_PROCESO',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $personas[0]->id,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        // 1. Derivar
        $derivaciones = $workflowService->derivar([
            'id_hoja_ruta' => $hr->id,
            'id_funcionario_origen' => $personas[0]->id,
            'id_unidad_origen' => $unidad->id,
            'proveido' => 'PARA SU REVISIÓN',
            'dias_plazo' => 2,
            'destinatarios' => [
                [
                    'id_funcionario_destino' => $personas[1]->id,
                    'id_unidad_destino' => $unidad->id,
                    'id_cargo_destino' => $puesto->id,
                    'es_copia' => false,
                ],
            ],
        ]);

        $der = $derivaciones[0];
        $this->assertEquals('PENDIENTE_RECEPCION', $der->estado_derivacion);

        // 2. Anular / Deshacer derivación en tránsito
        $resAnular = $workflowService->anularDerivacion($der->id, $personas[0]->id);
        $this->assertTrue($resAnular);
        $this->assertEquals('ANULADO', $der->fresh()->estado_derivacion);
        $this->assertEquals($personas[0]->id, $hr->fresh()->esta_con['id_funcionario']);

        // 3. Re-derivar y Recepcionar
        $derivaciones2 = $workflowService->derivar([
            'id_hoja_ruta' => $hr->id,
            'id_funcionario_origen' => $personas[0]->id,
            'id_unidad_origen' => $unidad->id,
            'destinatarios' => [
                [
                    'id_funcionario_destino' => $personas[1]->id,
                    'id_unidad_destino' => $unidad->id,
                    'es_copia' => false,
                ],
            ],
        ]);
        $der2 = $derivaciones2[0];
        $workflowService->recepcionar($der2->id, $personas[1]->id);
        $this->assertEquals('RECIBIDO', $der2->fresh()->estado_derivacion);

        // 4. Devolver con observación
        $retorno = $workflowService->devolver($der2->id, $personas[1]->id, 'Documentación incompleta: faltan firmas de respaldo.');
        $this->assertEquals('DEVUELTO_OBSERVADO', $der2->fresh()->estado_derivacion);
        $this->assertEquals('PENDIENTE_RECEPCION', $retorno->estado_derivacion);
    }

    // =========================================================================
    // SUBMÓDULO 3: BANDEJAS DE HOJAS DE RUTA Y ACCIONES PERMITIDAS
    // =========================================================================
    public function test_submodulo_3_bandeja_hojas_ruta_y_acciones_permitidas(): void
    {
        $response = $this->withoutMiddleware()->getJson('/api/correspondencia/hojas-ruta/bandeja?tipo_bandeja=ENTRADA');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertIsArray($response->json('data'));

        $hr = HojaRuta::first();
        if ($hr) {
            $responseAcciones = $this->withoutMiddleware()->getJson("/api/correspondencia/hojas-ruta/{$hr->id}/acciones-permitidas");
            $responseAcciones->assertStatus(200);
            $responseAcciones->assertJsonPath('success', true);
            $this->assertIsArray($responseAcciones->json('data.acciones'));
        }
    }

    // =========================================================================
    // SUBMÓDULO 4: CICLO DE VIDA DE DOCUMENTOS Y ADJUNTOS CON HASH SHA-256
    // =========================================================================
    public function test_submodulo_4_documentos_borrador_edicion_y_adjuntos(): void
    {
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();

        // 1. Crear Borrador
        $payloadDoc = [
            'tipo_documento' => 'NOTA_INTERNA',
            'id_unidad_generadora' => $unidad->id,
            'asunto' => 'SOLICITUD DE MATERIAL DE ESCRITORIO',
            'contenido_html' => '<p>Se solicita provisión de papel bond y archivadores de palanca.</p>',
            'enviar_a_revision' => false,
            'participantes' => [
                [
                    'tipo_participacion' => 'REMITENTE_DE',
                    'id_persona' => $persona->id,
                    'id_unidad' => $unidad->id,
                ],
            ],
        ];

        $responseDoc = $this->withoutMiddleware()->postJson('/api/correspondencia/documentos', $payloadDoc);
        $responseDoc->assertStatus(201);
        $docId = $responseDoc->json('data.id');
        $this->assertEquals('BORRADOR', $responseDoc->json('data.estado'));

        // 2. Editar Borrador
        $responseUpdate = $this->withoutMiddleware()->putJson("/api/correspondencia/documentos/{$docId}", [
            'asunto' => 'SOLICITUD DE MATERIAL DE ESCRITORIO Y TONERS',
            'contenido_html' => '<p>Se solicita provisión de papel bond y 4 toners para impresora HP LaserJet.</p>',
        ]);
        $responseUpdate->assertStatus(200);
        $this->assertEquals('SOLICITUD DE MATERIAL DE ESCRITORIO Y TONERS', $responseUpdate->json('data.asunto'));

        // 3. Adjuntos con SHA-256
        $adjunto = ArchivoAdjunto::create([
            'id_documento' => $docId,
            'nombre_original' => 'Cotizacion_Materiales.pdf',
            'ruta_almacenamiento' => 'correspondencia/adjuntos/cotizacion.pdf',
            'mime_type' => 'application/pdf',
            'tamanio_bytes' => 512000,
            'hash_sha256' => hash('sha256', 'Cotizacion_Materiales.pdf_test'),
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);
        $this->assertNotEmpty($adjunto->hash_sha256);
    }

    // =========================================================================
    // SUBMÓDULO 5: BANDEJA DE DOCUMENTOS
    // =========================================================================
    public function test_submodulo_5_bandeja_documentos_y_filtros_por_estado(): void
    {
        $response = $this->withoutMiddleware()->getJson('/api/correspondencia/documentos/bandeja?estado=FIRMADOS');
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertIsArray($response->json('data'));
    }

    // =========================================================================
    // SUBMÓDULO 6: FIRMAS SECUENCIALES (VIA ANTES DE DE) Y PIN DIGITAL
    // =========================================================================
    public function test_submodulo_6_firmas_secuenciales_y_bloqueo_prematuro(): void
    {
        $personas = Persona::take(2)->get();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        $firmaService = new FirmaDigitalService(new CiteGeneratorService);

        $doc = Documento::create([
            'tipo_documento' => 'MEMORANDUM',
            'asunto' => 'CONCESIÓN DE VACACIONES COLECTIVAS',
            'contenido_html' => '<p>Se comunica el cronograma oficial de vacaciones colectivas.</p>',
            'estado' => 'EN_REVISION',
            'id_unidad_generadora' => $unidad->id,
            'id_creador' => $personas[0]->id,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        // Participante 1: VIA (Revisor)
        ParticipanteDocumento::create([
            'id_documento' => $doc->id,
            'id_persona' => $personas[1]->id,
            'id_puesto' => $puesto->id,
            'tipo_participacion' => 'VIA',
            'orden_participacion' => 1,
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        // Participante 2: REMITENTE_DE (Autor)
        ParticipanteDocumento::create([
            'id_documento' => $doc->id,
            'id_persona' => $personas[0]->id,
            'id_puesto' => $puesto->id,
            'tipo_participacion' => 'REMITENTE_DE',
            'orden_participacion' => 2,
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        // Intentar firmar como DE antes que el VIA (Debe fallar con HTTP 412)
        $fallo = false;
        try {
            $firmaService->firmarDocumento($doc->id, $personas[0]->id, '1234', 'PIN_ELECTRONICO');
        } catch (PreconditionFailedHttpException $e) {
            $fallo = true;
        }
        $this->assertTrue($fallo, 'El sistema debe bloquear la firma del autor si el VIA no ha dado visto bueno.');

        // Revisor VIA firma
        $resVia = $firmaService->firmarDocumento($doc->id, $personas[1]->id, '1234', 'PIN_ELECTRONICO');
        $this->assertEquals('FIRMADO', $resVia->estado);

        // Ahora el autor DE firma
        $resDe = $firmaService->firmarDocumento($doc->id, $personas[0]->id, '1234', 'PIN_ELECTRONICO');
        $this->assertEquals('FIRMADO', $doc->fresh()->estado);
        $this->assertNotEmpty($doc->fresh()->cite);
        $this->assertNotEmpty($doc->fresh()->codigo_verificacion);
    }

    // =========================================================================
    // SUBMÓDULO 7: REVISIONES Y OBSERVACIONES
    // =========================================================================
    public function test_submodulo_7_observar_documento_y_retorno_al_creador(): void
    {
        $personas = Persona::take(2)->get();
        $unidad = UnidadOrganizacional::first();

        $doc = Documento::create([
            'tipo_documento' => 'INFORME_TECNICO',
            'asunto' => 'INFORME DE MANTENIMIENTO DE SILOS',
            'contenido_html' => '<p>Detalle de mantenimiento...</p>',
            'estado' => 'EN_REVISION',
            'id_unidad_generadora' => $unidad->id,
            'id_creador' => $personas[0]->id,
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        ParticipanteDocumento::create([
            'id_documento' => $doc->id,
            'id_persona' => $personas[1]->id,
            'tipo_participacion' => 'VIA',
            'orden_participacion' => 1,
            'bandeja' => 'EN_REVISION',
            '_usuario_creacion' => 1,
            '_fecha_creacion' => now(),
        ]);

        $responseObservar = $this->withoutMiddleware()->postJson('/api/correspondencia/firmas/rechazar', [
            'id_documento' => $doc->id,
            'id_persona' => $personas[1]->id,
            'motivo' => 'Falta adjuntar el cuadro de cotizaciones comerciales.',
        ]);
        $responseObservar->assertStatus(200);
        $this->assertEquals('OBSERVADO', $doc->fresh()->estado);
    }

    // =========================================================================
    // SUBMÓDULO 8: SEGUIMIENTO, LÍNEA DE TIEMPO Y RASTREO PÚBLICO
    // =========================================================================
    public function test_submodulo_8_trazabilidad_cronologica_y_publica(): void
    {
        $hr = HojaRuta::first();

        // 1. Timeline interno
        $response = $this->withoutMiddleware()->getJson("/api/correspondencia/seguimiento/{$hr->id}/timeline");
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data.timeline'));

        // 2. Rastreo público ciudadano por CITE
        $responsePublic = $this->getJson("/api/correspondencia/publico/seguimiento/{$hr->nro_hoja_ruta}");
        $responsePublic->assertStatus(200);
        $responsePublic->assertJsonPath('success', true);
        $responsePublic->assertJsonPath('data.hoja_ruta.nro_hoja_ruta', $hr->nro_hoja_ruta);
    }

    // =========================================================================
    // SUBMÓDULO 9: VENTANILLA ÚNICA EXTERNA
    // =========================================================================
    public function test_submodulo_9_ventanilla_unica_ingreso_tramite_externo(): void
    {
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();
        $puesto = Puesto::first();

        $payload = [
            'remitente_externo' => 'CÁMARA AGROPECUARIA DEL ORIENTE (CAO)',
            'asunto' => 'SOLICITUD DE REUNIÓN DE COORDINACIÓN DE SIEMBRA DE VERANO',
            'nro_fojas' => 10,
            'nro_anexos' => 2,
            'prioridad' => 'ALTA',
            'id_unidad_destino' => $unidad->id,
            'id_persona_destino' => $persona->id,
            'id_cargo_destino' => $puesto->id,
            'proveido' => 'AGENDAR EN REUNIÓN DE DIRECTORIO',
        ];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/ventanillas/entrada', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.hojas_ruta', [
            'tipo_hr' => 'EXTERNA',
            'remitente_externo' => 'CÁMARA AGROPECUARIA DEL ORIENTE (CAO)',
        ]);
    }

    // =========================================================================
    // SUBMÓDULO 10: DESPACHOS DE SALIDA EXTERNA Y ACUSES DE RECIBO
    // =========================================================================
    public function test_submodulo_10_despachos_salida_courier_y_entrega_con_acuse(): void
    {
        $hr = HojaRuta::first();

        $payload = [
            'id_hoja_ruta' => $hr->id,
            'tipo_despacho' => 'COURIER_POSTAL',
            'destinatario_institucion' => 'AUTORIDAD DE FISCALIZACIÓN DE EMPRESAS (AEMP)',
            'destinatario_persona' => 'DIRECTOR EJECUTIVO',
            'destinatario_direccion' => 'Calle Batallón Colorados Nro. 24 Edif. El Cóndor',
            'destinatario_ciudad' => 'La Paz',
            'empresa_courier' => 'AGENCIA BOLIVIANA DE CORREOS',
            'nro_guia_despacho' => 'GUIA-EXH-99201',
        ];

        $resDespacho = $this->withoutMiddleware()->postJson('/api/correspondencia/despachos', $payload);
        $resDespacho->assertStatus(201);
        $idDespacho = $resDespacho->json('data.id');

        $resEntrega = $this->withoutMiddleware()->postJson("/api/correspondencia/despachos/{$idDespacho}/entregar", [
            'observaciones' => 'Entregado con firma y sello oficial en ventanilla de la AEMP.',
        ]);
        $resEntrega->assertStatus(200);
        $this->assertEquals('ENTREGADO_CON_ACUSE', $resEntrega->json('data.estado_despacho'));
    }

    // =========================================================================
    // SUBMÓDULO 11: SOLICITUDES CIUDADANAS (PORTAL WEB Y ADMISIÓN)
    // =========================================================================
    public function test_submodulo_11_solicitudes_ciudadanas_portal_y_conversion(): void
    {
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();

        // 1. Registro público desde el portal
        $payloadPub = [
            'solicitante_nombre' => 'COOPERATIVA AGRÍCOLA INTEGRAL SAN JULIÁN',
            'solicitante_ci_nit' => '99887766',
            'solicitante_telefono' => '71122334',
            'solicitante_correo' => 'cooperativa.sanjulian@gmail.com',
            'tipo_solicitud' => 'VENTA DE GRANO DE MAÍZ',
            'descripcion_solicitud' => 'Ofrecemos 10.000 quintales de maíz amarillo duro para la planta de balanceados.',
        ];

        $responsePub = $this->postJson('/api/correspondencia/publico/solicitud-ciudadana', $payloadPub);
        $responsePub->assertStatus(201);
        $solId = $responsePub->json('data.id');

        // 2. Funcionario admite y convierte en Hoja de Ruta
        $responseAdmitir = $this->withoutMiddleware()->postJson("/api/correspondencia/solicitudes-ciudadanas/{$solId}/convertir-hoja-ruta", [
            'id_unidad_destino' => $unidad->id,
            'id_funcionario_destino' => $persona->id,
            'proveido' => 'EVALUAR PROPUESTA DE COMPRA',
            'dias_plazo' => 3,
        ]);
        $responseAdmitir->assertStatus(200);
        $this->assertEquals('HOJA_RUTA_GENERADA', $responseAdmitir->json('data.solicitud.estado_solicitud'));
        $this->assertNotEmpty($responseAdmitir->json('data.hoja_ruta.nro_hoja_ruta'));
    }

    // =========================================================================
    // SUBMÓDULO 12: GESTOR DE PLANTILLAS Y CONFIGURACIÓN DOCUMENTAL
    // =========================================================================
    public function test_submodulo_12_disenador_plantillas_y_componentes_modulares(): void
    {
        $payload = [
            'nombre' => 'RESOLUCIÓN ADMINISTRATIVA GENERAL',
            'sigla' => 'RAG',
            'version' => 1,
            'param_tipo_plantilla' => 'RESOLUCION',
            'componentes' => [
                [
                    'nombre' => 'Vistos y Considerando',
                    'tipo_componente' => 'TEXTO_HTML',
                    'es_obligatorio' => true,
                ],
                [
                    'nombre' => 'Por Tanto (Resuelve)',
                    'tipo_componente' => 'TEXTO_HTML',
                    'es_obligatorio' => true,
                ],
                [
                    'nombre' => 'Pie de Firmas de Autoridades',
                    'tipo_componente' => 'PIE_FIRMAS',
                    'es_obligatorio' => true,
                ],
            ],
        ];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/configuracion/plantillas', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.plantillas_documentos', ['sigla' => 'RAG']);
    }

    // =========================================================================
    // SUBMÓDULO 13: ETIQUETAS Y CARPETAS VIRTUALES
    // =========================================================================
    public function test_submodulo_13_organizacion_carpetas_y_etiquetas(): void
    {
        $hr = HojaRuta::first();

        // 1. Crear etiqueta
        $resCrear = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas', [
            'nombre' => 'PROYECTOS INDUSTRIALIZACIÓN 2026',
            'color' => '#00796B',
        ]);
        $resCrear->assertStatus(201);
        $idEtiqueta = $resCrear->json('data.id');

        // 2. Asignar
        $resAsignar = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas/asignar', [
            'id_etiqueta' => $idEtiqueta,
            'id_hoja_ruta' => $hr->id,
        ]);
        $resAsignar->assertStatus(200);

        // 3. Desasignar
        $resDes = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas/desasignar', [
            'id_etiqueta' => $idEtiqueta,
            'id_hoja_ruta' => $hr->id,
        ]);
        $resDes->assertStatus(200);
    }

    // =========================================================================
    // SUBMÓDULO 14: TRANSFERENCIAS MASIVAS DE BANDEJAS POR DESVINCULACIÓN/CAMBIO
    // =========================================================================
    public function test_submodulo_14_transferencias_masivas_de_bandejas(): void
    {
        $personas = Persona::take(2)->get();

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/transferencias', [
            'id_funcionario_origen' => $personas[0]->id,
            'id_funcionario_destino' => $personas[1]->id,
            'motivo' => 'Transferencia integral por promoción a Gerencia Regional.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    // =========================================================================
    // SUBMÓDULO 15: MATRIZ DE PERMISOS DE DERIVACIÓN INTER-UNIDADES
    // =========================================================================
    public function test_submodulo_15_matriz_permisos_derivacion_inter_unidades(): void
    {
        $unidades = UnidadOrganizacional::take(2)->get();

        $payload = [
            'id_origen' => $unidades[0]->id,
            'tipo_origen' => 'UNIDAD',
            'destinos' => [
                [
                    'id_destino' => $unidades[1]->id,
                    'tipo_destino' => 'UNIDAD',
                    'permitido' => true,
                ],
            ],
        ];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/permisos/guardar-lote', $payload);
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }
}
