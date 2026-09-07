<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Services\Correspondencia\CaratulaPdfService;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CorrespondenciaWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cite_generation_is_atomic_and_formatted_correctly(): void
    {
        $service = app(CiteGeneratorService::class);
        $unidad = UnidadOrganizacional::first();
        if (! $unidad) {
            return;
        }

        $cite = $service->generarCiteHojaRuta();
        $this->assertNotEmpty($cite);
        $this->assertStringContainsString('HR-EMAPA', $cite);

        $citeDoc = $service->generarCiteDocumento($unidad->id, 'MEM');
        $this->assertNotEmpty($citeDoc);
        $this->assertStringContainsString('MEM', $citeDoc);
    }

    public function test_crear_hoja_ruta_con_derivacion_inmediata(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if (! $persona || ! $unidad || ! $puesto) {
            return;
        }

        $payload = [
            'tipo_hr' => 'INTERNA',
            'asunto' => 'SOLICITUD DE COMPRA DIRECTA DE MAÍZ',
            'prioridad' => 'ALTA',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'id_cargo_origen' => $puesto->id,
            'proveido' => 'PASE A SUS EFECTOS Y REVISIÓN TÉCNICA',
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'id_cargo_destino' => $puesto->id,
                    'es_copia' => false,
                ],
            ],
        ];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/hojas-ruta', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.hojas_ruta', [
            'asunto' => 'SOLICITUD DE COMPRA DIRECTA DE MAÍZ',
            'prioridad' => 'ALTA',
        ]);
    }

    public function test_recepcion_y_devolucion_de_derivacion(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if (! $persona || ! $unidad || ! $puesto) {
            return;
        }

        $workflowService = app(DerivacionWorkflowService::class);
        $citeService = app(CiteGeneratorService::class);

        // Crear HR
        $hr = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'asunto' => 'TRÁMITE DE PRUEBA PARA DEVOLUCIÓN',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'id_cargo_origen' => $puesto->id,
            'estado' => 'EN_PROCESO',
            '_usuario_creacion' => 1,
        ]);

        $derivaciones = $workflowService->derivar([
            'id_hoja_ruta' => $hr->id,
            'id_unidad_origen' => $unidad->id,
            'id_funcionario_origen' => $persona->id,
            'id_cargo_origen' => $puesto->id,
            'proveido' => 'PASE A REVISIÓN',
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'id_cargo_destino' => $puesto->id,
                    'es_copia' => false,
                ],
            ],
        ]);

        $derivacion = $derivaciones[0];

        // 1. Recepcionar
        $responseRec = $this->withoutMiddleware()->postJson("/api/correspondencia/derivaciones/{$derivacion->id}/recibir");
        $responseRec->assertStatus(200);
        $responseRec->assertJsonPath('success', true);
        $this->assertEquals('RECIBIDO', $derivacion->fresh()->estado_derivacion);

        // 2. Devolver con observaciones
        $responseDev = $this->withoutMiddleware()->postJson("/api/correspondencia/derivaciones/{$derivacion->id}/devolver", [
            'motivo' => 'Falta documentación de respaldo técnico.',
        ]);
        $responseDev->assertStatus(200);
        $responseDev->assertJsonPath('success', true);
        $this->assertEquals('DEVUELTO_OBSERVADO', $derivacion->fresh()->estado_derivacion);
    }

    public function test_anulacion_deshacer_derivacion_en_transito(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if (! $persona || ! $unidad || ! $puesto) {
            return;
        }

        $workflowService = app(DerivacionWorkflowService::class);
        $citeService = app(CiteGeneratorService::class);

        $hr = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'asunto' => 'TRÁMITE PARA PROBAR ANULACIÓN DE DERIVACIÓN',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'id_cargo_origen' => $puesto->id,
            'estado' => 'EN_PROCESO',
            '_usuario_creacion' => 1,
        ]);

        $derivaciones = $workflowService->derivar([
            'id_hoja_ruta' => $hr->id,
            'id_unidad_origen' => $unidad->id,
            'id_funcionario_origen' => $persona->id,
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'es_copia' => false,
                ],
            ],
        ]);

        $derivacion = $derivaciones[0];
        $this->assertEquals('PENDIENTE_RECEPCION', $derivacion->estado_derivacion);

        // Anular derivación antes de que sea recibida
        $responseAnular = $this->withoutMiddleware()->postJson("/api/correspondencia/derivaciones/{$derivacion->id}/anular", [
            'id_persona' => $persona->id,
        ]);
        $responseAnular->assertStatus(200);
        $responseAnular->assertJsonPath('success', true);

        $this->assertEquals('ANULADO', $derivacion->fresh()->estado_derivacion);
    }

    public function test_derivacion_con_multiples_copias_cc(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if (! $persona || ! $unidad || ! $puesto) {
            return;
        }

        $workflowService = app(DerivacionWorkflowService::class);
        $citeService = app(CiteGeneratorService::class);

        $hr = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(),
            'gestion' => 2026,
            'tipo_hr' => 'INTERNA',
            'asunto' => 'INFORME CON COPIAS A DIRECCIÓN GENERAL',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'estado' => 'EN_PROCESO',
            '_usuario_creacion' => 1,
        ]);

        $derivaciones = $workflowService->derivar([
            'id_hoja_ruta' => $hr->id,
            'id_unidad_origen' => $unidad->id,
            'id_funcionario_origen' => $persona->id,
            'proveido' => 'PARA SU CONOCIMIENTO Y FINES CONSIGUIENTES',
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'es_copia' => false, // Principal
                ],
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'es_copia' => true, // Copia CC
                ],
            ],
        ]);

        $this->assertCount(2, $derivaciones);
        $this->assertFalse((bool) $derivaciones[0]->es_copia);
        $this->assertTrue((bool) $derivaciones[1]->es_copia);
    }

    public function test_agrupacion_y_desagrupacion_de_expedientes(): void
    {
        $citeService = app(CiteGeneratorService::class);
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();
        if (! $unidad || ! $persona) {
            return;
        }

        $madre = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(),
            'gestion' => 2026,
            'asunto' => 'EXPEDIENTE MADRE DE CONTRATACIÓN',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'estado' => 'EN_PROCESO',
            '_usuario_creacion' => 1,
        ]);

        $hija = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(),
            'gestion' => 2026,
            'asunto' => 'EXPEDIENTE HIJO DE ANTECEDENTES TÉCNICOS',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'estado' => 'EN_PROCESO',
            '_usuario_creacion' => 1,
        ]);

        // 1. Agrupar
        $responseAgrupar = $this->withoutMiddleware()->postJson('/api/correspondencia/hojas-ruta/agrupar', [
            'id_hoja_ruta_principal' => $madre->id,
            'id_hoja_ruta_anexada' => $hija->id,
            'motivo' => 'Anexar antecedentes técnicos complementarios.',
        ]);
        $responseAgrupar->assertStatus(201);
        $responseAgrupar->assertJsonPath('success', true);

        $idAgrupacion = $responseAgrupar->json('data.id');
        $this->assertEquals('AGRUPADO', $hija->fresh()->estado);

        // 2. Desagrupar
        $responseDesagrupar = $this->withoutMiddleware()->postJson("/api/correspondencia/hojas-ruta/{$idAgrupacion}/desagrupar");
        $responseDesagrupar->assertStatus(200);
        $responseDesagrupar->assertJsonPath('success', true);
        $this->assertEquals('EN_PROCESO', $hija->fresh()->estado);
    }

    public function test_concluir_y_reabrir_hoja_ruta(): void
    {
        $citeService = app(CiteGeneratorService::class);
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();
        if (! $unidad || ! $persona) {
            return;
        }

        $hr = HojaRuta::create([
            'nro_hoja_ruta' => $citeService->generarCiteHojaRuta(),
            'gestion' => 2026,
            'asunto' => 'TRÁMITE DE PRUEBA PARA CONCLUSIÓN Y REAPERTURA',
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'estado' => 'EN_PROCESO',
            '_usuario_creacion' => 1,
        ]);

        // 1. Concluir / Archivar
        $responseCerrar = $this->withoutMiddleware()->postJson("/api/correspondencia/hojas-ruta/{$hr->id}/cerrar", [
            'motivo_cierre' => 'Trámite completamente concluido.',
        ]);
        $responseCerrar->assertStatus(200);
        $this->assertEquals('CERRADO', $hr->fresh()->estado);

        // 2. Reabrir
        $responseReabrir = $this->withoutMiddleware()->postJson("/api/correspondencia/hojas-ruta/{$hr->id}/reabrir", [
            'motivo_reapertura' => 'Reapertura para adjuntar comprobante final.',
        ]);
        $responseReabrir->assertStatus(200);
        $this->assertEquals('EN_PROCESO', $hr->fresh()->estado);
    }

    public function test_redactar_documento_borrador_y_editar(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if (! $persona || ! $unidad || ! $puesto) {
            return;
        }

        // 1. Redactar Borrador
        $payloadDoc = [
            'tipo_documento' => 'NOTA_INTERNA',
            'id_unidad_generadora' => $unidad->id,
            'asunto' => 'SOLICITUD DE MATERIALES DE ESCRITORIO',
            'contenido_html' => '<p>Se solicita material de escritorio para el área técnica...</p>',
            'enviar_a_revision' => false,
            'participantes' => [
                [
                    'tipo_participacion' => 'REMITENTE_DE',
                    'id_persona' => $persona->id,
                    'id_puesto' => $puesto->id,
                    'id_unidad' => $unidad->id,
                ],
            ],
        ];

        $responseDoc = $this->withoutMiddleware()->postJson('/api/correspondencia/documentos', $payloadDoc);
        $responseDoc->assertStatus(201);
        $docId = $responseDoc->json('data.id');
        $this->assertEquals('BORRADOR', $responseDoc->json('data.estado'));

        // 2. Actualizar / Editar
        $responseUpdate = $this->withoutMiddleware()->putJson("/api/correspondencia/documentos/{$docId}", [
            'asunto' => 'SOLICITUD DE MATERIALES DE ESCRITORIO Y TONERS',
            'contenido_html' => '<p>Se solicita material de escritorio y toners actualizados...</p>',
            'enviar_a_revision' => true,
        ]);
        $responseUpdate->assertStatus(200);
        $this->assertEquals('EN_REVISION', $responseUpdate->json('data.estado'));
    }

    public function test_flujo_secuencial_de_firmas_via_antes_de_emision(): void
    {
        $personas = Persona::take(2)->get();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if ($personas->count() < 2 || ! $unidad || ! $puesto) {
            return;
        }

        $autor = $personas[0];
        $revisorVia = $personas[1];

        // Crear documento con participante VIA y REMITENTE_DE
        $payloadDoc = [
            'tipo_documento' => 'INFORME_TECNICO',
            'id_unidad_generadora' => $unidad->id,
            'asunto' => 'INFORME TÉCNICO DE INSPECCIÓN EN PLANTA',
            'contenido_html' => '<p>Resultado de la inspección técnica realizada en silos...</p>',
            'enviar_a_revision' => true,
            'participantes' => [
                [
                    'tipo_participacion' => 'VIA',
                    'id_persona' => $revisorVia->id,
                    'id_puesto' => $puesto->id,
                    'id_unidad' => $unidad->id,
                ],
                [
                    'tipo_participacion' => 'REMITENTE_DE',
                    'id_persona' => $autor->id,
                    'id_puesto' => $puesto->id,
                    'id_unidad' => $unidad->id,
                ],
            ],
        ];

        $responseDoc = $this->withoutMiddleware()->postJson('/api/correspondencia/documentos', $payloadDoc);
        $responseDoc->assertStatus(201);
        $docId = $responseDoc->json('data.id');

        // Intento de firma del REMITENTE_DE sin que VIA haya firmado (debe fallar con 412)
        $responseFirmaPrematura = $this->withoutMiddleware()->postJson('/api/correspondencia/firmas/firmar', [
            'id_documento' => $docId,
            'id_persona' => $autor->id,
            'pin' => '1234',
        ]);
        $responseFirmaPrematura->assertStatus(412);

        // 1. Firma del Revisor VIA
        $responseFirmaVia = $this->withoutMiddleware()->postJson('/api/correspondencia/firmas/firmar', [
            'id_documento' => $docId,
            'id_persona' => $revisorVia->id,
            'pin' => '1234',
        ]);
        $responseFirmaVia->assertStatus(200);

        // 2. Ahora el REMITENTE_DE firma con éxito
        $responseFirmaAutor = $this->withoutMiddleware()->postJson('/api/correspondencia/firmas/firmar', [
            'id_documento' => $docId,
            'id_persona' => $autor->id,
            'pin' => '1234',
        ]);
        $responseFirmaAutor->assertStatus(200);

        $this->assertEquals('FIRMADO', Documento::find($docId)->estado);
    }

    public function test_observar_documento_y_retorno_al_autor(): void
    {
        $personas = Persona::take(2)->get();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if ($personas->count() < 2 || ! $unidad || ! $puesto) {
            return;
        }

        $autor = $personas[0];
        $revisor = $personas[1];

        $payloadDoc = [
            'tipo_documento' => 'MEMORANDUM',
            'id_unidad_generadora' => $unidad->id,
            'asunto' => 'MEMORANDUM DE LLAMADA DE ATENCIÓN',
            'contenido_html' => '<p>Por no cumplir el horario...</p>',
            'enviar_a_revision' => true,
            'participantes' => [
                [
                    'tipo_participacion' => 'VIA',
                    'id_persona' => $revisor->id,
                    'id_puesto' => $puesto->id,
                    'id_unidad' => $unidad->id,
                ],
                [
                    'tipo_participacion' => 'REMITENTE_DE',
                    'id_persona' => $autor->id,
                    'id_puesto' => $puesto->id,
                    'id_unidad' => $unidad->id,
                ],
            ],
        ];

        $responseDoc = $this->withoutMiddleware()->postJson('/api/correspondencia/documentos', $payloadDoc);
        $docId = $responseDoc->json('data.id');

        // Revisor observa el documento
        $responseObservar = $this->withoutMiddleware()->postJson('/api/correspondencia/firmas/rechazar', [
            'id_documento' => $docId,
            'id_persona' => $revisor->id,
            'motivo' => 'Falta adjuntar el reporte biométrico como respaldo.',
        ]);
        $responseObservar->assertStatus(200);
        $this->assertEquals('OBSERVADO', Documento::find($docId)->estado);
    }

    public function test_anulacion_formal_de_documento(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $puesto = Puesto::first();
        if (! $persona || ! $unidad || ! $puesto) {
            return;
        }

        $payloadDoc = [
            'tipo_documento' => 'CIRCULAR',
            'id_unidad_generadora' => $unidad->id,
            'asunto' => 'CIRCULAR DE PRUEBA PARA ANULAR',
            'contenido_html' => '<p>Contenido a anular...</p>',
            'participantes' => [
                [
                    'tipo_participacion' => 'REMITENTE_DE',
                    'id_persona' => $persona->id,
                    'id_puesto' => $puesto->id,
                    'id_unidad' => $unidad->id,
                ],
            ],
        ];

        $responseDoc = $this->withoutMiddleware()->postJson('/api/correspondencia/documentos', $payloadDoc);
        $docId = $responseDoc->json('data.id');

        $responseAnular = $this->withoutMiddleware()->postJson("/api/correspondencia/documentos/{$docId}/anular", [
            'motivo' => 'Decreto institucional modificado, circular sin efecto.',
        ]);
        $responseAnular->assertStatus(200);
        $this->assertEquals('ANULADO', Documento::find($docId)->estado);
    }

    public function test_caratula_pdf_html_generates_qr_and_official_content(): void
    {
        $hr = HojaRuta::first();
        if (! $hr) {
            return;
        }

        $service = app(CaratulaPdfService::class);
        $html = $service->renderCaratulaHtml($hr);

        $this->assertStringContainsString('EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS', $html);
        $this->assertStringContainsString($hr->nro_hoja_ruta, $html);
        $this->assertStringContainsString('create-qr-code', $html);
    }

    public function test_verificacion_publica_de_documento(): void
    {
        $doc = Documento::whereNotNull('codigo_verificacion')->first();
        if (! $doc) {
            return;
        }

        $response = $this->getJson("/api/correspondencia/publico/verificar-documento/{$doc->codigo_verificacion}");
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.cite', $doc->cite);
    }

    public function test_ventanilla_unica_recepcion_externa(): void
    {
        $unidad = UnidadOrganizacional::first();
        $persona = Persona::first();
        $puesto = Puesto::first();
        if (! $unidad || ! $persona || ! $puesto) {
            return;
        }

        $payload = [
            'remitente_externo' => 'ASOCIACIÓN NACIONAL DE PRODUCTORES DE OLEAGINOSAS (ANAPO)',
            'asunto' => 'PROPUESTA DE PROVISIÓN DE GRANO DE SOYA CAMPAÑA 2026',
            'nro_fojas' => 24,
            'nro_anexos' => 3,
            'prioridad' => 'ALTA',
            'id_unidad_destino' => $unidad->id,
            'id_persona_destino' => $persona->id,
            'id_cargo_destino' => $puesto->id,
            'proveido' => 'PASE A SUS EFECTOS DE EVALUACIÓN TÉCNICA',
        ];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/ventanillas/entrada', $payload);
        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.hojas_ruta', [
            'tipo_hr' => 'EXTERNA',
            'remitente_externo' => 'ASOCIACIÓN NACIONAL DE PRODUCTORES DE OLEAGINOSAS (ANAPO)',
        ]);
    }

    public function test_seguimiento_timeline_y_rastreo_publico(): void
    {
        $hr = HojaRuta::first();
        if (! $hr) {
            return;
        }

        // 1. Seguimiento autenticado
        $response = $this->withoutMiddleware()->getJson("/api/correspondencia/seguimiento/{$hr->id}/timeline");
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertNotEmpty($response->json('data.timeline'));

        // 2. Rastreo público por CITE
        $responsePublic = $this->getJson("/api/correspondencia/publico/seguimiento/{$hr->nro_hoja_ruta}");
        $responsePublic->assertStatus(200);
        $responsePublic->assertJsonPath('success', true);
        $responsePublic->assertJsonPath('data.hoja_ruta.nro_hoja_ruta', $hr->nro_hoja_ruta);
    }

    public function test_despacho_salida_externa_y_entrega_con_acuse(): void
    {
        $hr = HojaRuta::first();
        if (! $hr) {
            return;
        }

        $payload = [
            'id_hoja_ruta' => $hr->id,
            'tipo_despacho' => 'COURIER_POSTAL',
            'destinatario_institucion' => 'MINISTERIO DE DESARROLLO PRODUCTIVO Y ECONOMÍA PLURAL',
            'destinatario_persona' => 'MINISTRO DE ESTADO',
            'destinatario_direccion' => 'Av. Mariscal Santa Cruz, Edif. Centro de Comunicaciones',
            'destinatario_ciudad' => 'La Paz',
            'empresa_courier' => 'AGENCIA BOLIVIANA DE CORREOS',
            'nro_guia_despacho' => 'GUIA-BO-2026-99881',
        ];

        // 1. Registrar despacho
        $responseDespacho = $this->withoutMiddleware()->postJson('/api/correspondencia/despachos', $payload);
        $responseDespacho->assertStatus(201);
        $idDespacho = $responseDespacho->json('data.id');

        // 2. Registrar entrega con acuse
        $responseEntrega = $this->withoutMiddleware()->postJson("/api/correspondencia/despachos/{$idDespacho}/entregar", [
            'observaciones' => 'Entregado en ventanilla ministerial con sello de recepción.',
        ]);
        $responseEntrega->assertStatus(200);
        $this->assertEquals('ENTREGADO_CON_ACUSE', $responseEntrega->json('data.estado_despacho'));
    }

    public function test_transferencia_masiva_de_bandejas(): void
    {
        $personas = Persona::take(2)->get();
        if ($personas->count() < 2) {
            return;
        }

        $origen = $personas[0];
        $destino = $personas[1];

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/transferencias', [
            'id_funcionario_origen' => $origen->id,
            'id_funcionario_destino' => $destino->id,
            'motivo' => 'Transferencia por cambio de puesto y reasignación de funciones.',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    public function test_gestion_de_etiquetas_y_asignacion_a_hoja_ruta(): void
    {
        $hr = HojaRuta::first();
        if (! $hr) {
            return;
        }

        // 1. Crear etiqueta
        $responseCrear = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas', [
            'nombre' => 'AUDITORÍA ESPECIAL 2026',
            'color' => '#8E24AA',
        ]);
        $responseCrear->assertStatus(201);
        $idEtiqueta = $responseCrear->json('data.id');

        // 2. Asignar etiqueta a HR
        $responseAsignar = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas/asignar', [
            'id_etiqueta' => $idEtiqueta,
            'id_hoja_ruta' => $hr->id,
        ]);
        $responseAsignar->assertStatus(200);
        $responseAsignar->assertJsonPath('success', true);

        // 3. Desasignar
        $responseDes = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas/desasignar', [
            'id_etiqueta' => $idEtiqueta,
            'id_hoja_ruta' => $hr->id,
        ]);
        $responseDes->assertStatus(200);
    }

    public function test_plantilla_documental_y_componentes(): void
    {
        $responseList = $this->withoutMiddleware()->getJson('/api/correspondencia/configuracion/plantillas');
        $responseList->assertStatus(200);
        $responseList->assertJsonPath('success', true);

        $payload = [
            'nombre' => 'MEMORÁNDUM TÉCNICO REGIONAL',
            'sigla' => 'MEM-REG',
            'version' => '1.0',
            'param_tipo_plantilla' => 'PLANTILLA_DOCUMENTO_OFICIAL',
            'componentes' => [
                [
                    'nombre' => 'Encabezado Institucional',
                    'tipo_componente' => 'CABECERA',
                    'es_obligatorio' => true,
                ],
                [
                    'nombre' => 'Cuerpo Técnico',
                    'tipo_componente' => 'TEXTO_HTML',
                    'es_obligatorio' => true,
                ],
            ],
        ];

        $responseStore = $this->withoutMiddleware()->postJson('/api/correspondencia/configuracion/plantillas', $payload);
        $responseStore->assertStatus(200);
        $responseStore->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.plantillas_documentos', [
            'sigla' => 'MEM-REG',
        ]);
    }

    public function test_matriz_permisos_derivacion_inter_unidades(): void
    {
        $unidades = UnidadOrganizacional::take(2)->get();
        if ($unidades->count() < 2) {
            return;
        }

        $origen = $unidades[0];
        $destino = $unidades[1];

        // Guardar lote de permisos de derivación
        $payload = [
            'id_origen' => $origen->id,
            'tipo_origen' => 'UNIDAD',
            'destinos' => [
                [
                    'id_destino' => $destino->id,
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
