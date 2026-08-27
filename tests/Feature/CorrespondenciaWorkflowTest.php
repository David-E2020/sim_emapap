<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Correspondencia\AccesoCompartido;
use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\DespachoSalida;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\Etiqueta;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\ParticipanteDocumento;
use App\Models\Correspondencia\PlantillaDocumento;
use App\Models\Correspondencia\SolicitudCiudadana;
use App\Models\Correspondencia\Ventanilla;
use App\Models\Rrhh\Persona;
use App\Models\Rrhh\Puesto;
use App\Models\Rrhh\Regional;
use App\Models\Rrhh\UnidadOrganizacional;
use App\Models\User;
use App\Services\Correspondencia\CaratulaPdfService;
use App\Services\Correspondencia\CiteGeneratorService;
use App\Services\Correspondencia\DerivacionWorkflowService;
use App\Services\Correspondencia\FirmaDigitalService;
use Tests\TestCase;

class CorrespondenciaWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\Correspondencia\\CorrespondenciaMasterSeeder']);
    }

    public function test_cite_generation_is_atomic_and_formatted_correctly(): void
    {
        $citeService = app(CiteGeneratorService::class);

        $citeHR1 = $citeService->generarCiteHojaRuta(null, 2026);
        $citeHR2 = $citeService->generarCiteHojaRuta(null, 2026);

        $this->assertStringContainsString('HR-EMAPA-NAL-', $citeHR1);
        $this->assertStringContainsString('HR-EMAPA-NAL-', $citeHR2);
        $this->assertNotEquals($citeHR1, $citeHR2);

        $unidad = UnidadOrganizacional::first();
        $citeDoc1 = $citeService->generarCiteDocumento($unidad->id, 'MEM', 2026);
        $citeDoc2 = $citeService->generarCiteDocumento($unidad->id, 'MEM', 2026);

        $this->assertStringContainsString('EMAPA/', $citeDoc1);
        $this->assertStringContainsString('/MEM/', $citeDoc1);
        $this->assertNotEquals($citeDoc1, $citeDoc2);
    }

    public function test_crear_hoja_ruta_con_derivacion_inmediata(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/hojas-ruta', [
            'asunto' => 'SOLICITUD DE AUDITORÍA TÉCNICA DE ALMACENES',
            'prioridad' => 'ALTA',
            'nro_fojas' => 12,
            'nro_anexos' => 2,
            'id_unidad_origen' => $unidad->id,
            'id_persona_origen' => $persona->id,
            'proveido' => 'PASE A SUS EFECTOS',
            'instruccion_detalle' => 'Verificar existencias físicas en silos.',
            'dias_plazo' => 3,
            'destinatarios' => [
                [
                    'id_unidad_destino' => $unidad->id,
                    'id_funcionario_destino' => $persona->id,
                    'es_copia' => false,
                ]
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.hojas_ruta', [
            'asunto' => 'SOLICITUD DE AUDITORÍA TÉCNICA DE ALMACENES',
            'prioridad' => 'ALTA',
        ]);
        $this->assertDatabaseHas('correspondencia.derivaciones', [
            'proveido' => 'PASE A SUS EFECTOS',
            'estado_derivacion' => 'PENDIENTE_RECEPCION',
        ]);
    }

    public function test_recepcion_y_devolucion_de_derivacion(): void
    {
        $workflowService = app(DerivacionWorkflowService::class);
        $hr = HojaRuta::first();
        $persona = Persona::first();

        $derivaciones = $workflowService->derivar([
            'id_hoja_ruta' => $hr->id,
            'proveido' => 'PARA INFORME TÉCNICO CIRCUNSTANCIADO',
            'destinatarios' => [
                [
                    'id_unidad_destino' => 1,
                    'id_funcionario_destino' => $persona->id,
                    'es_copia' => false,
                ]
            ],
        ]);

        $idDerivacion = $derivaciones[0]->id;

        // Recepcionar
        $recibida = $workflowService->recibir($idDerivacion, $persona->id);
        $this->assertEquals('RECIBIDO', $recibida->estado_derivacion);
        $this->assertNotNull($recibida->fecha_recepcion);

        // Devolver con observaciones
        $retorno = $workflowService->devolver($idDerivacion, $persona->id, 'Falta firma del responsable técnico');
        $this->assertEquals('DEVUELTO CON OBSERVACIONES', $retorno->proveido);
        $this->assertEquals('Falta firma del responsable técnico', $retorno->instruccion_detalle);
    }

    public function test_redactar_documento_y_firmar_con_pin(): void
    {
        $persona = Persona::first();
        $unidad = UnidadOrganizacional::first();
        $plantilla = PlantillaDocumento::where('sigla', 'MEM')->first();

        // 1. Redactar documento
        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/documentos', [
            'tipo_documento' => 'MEMORANDUM',
            'id_plantilla' => $plantilla ? $plantilla->id : null,
            'id_unidad_generadora' => $unidad->id,
            'asunto' => 'MEMORÁNDUM DE COMISIÓN DE VIAJE',
            'contenido_html' => '<p>Se comisiona a su persona...</p>',
            'participantes' => [
                [
                    'id_persona' => $persona->id,
                    'tipo_participacion' => 'REMITENTE_DE',
                ]
            ],
        ]);

        $response->assertStatus(201);
        $idDoc = $response->json('data.id');

        // 2. Firmar con PIN
        $firmaService = app(FirmaDigitalService::class);
        $firma = $firmaService->firmarDocumento($idDoc, $persona->id, '1234', 'PIN_ELECTRONICO');

        $this->assertEquals('FIRMADO', $firma->estado);
        $this->assertNotNull($firma->hash_documento_sha256);
        $this->assertNotNull($firma->fecha_firma_aprobacion);

        $doc = Documento::find($idDoc);
        $this->assertEquals('FIRMADO', $doc->estado);
    }

    public function test_caratula_pdf_html_generates_qr_and_official_content(): void
    {
        $caratulaService = app(CaratulaPdfService::class);
        $hr = HojaRuta::with(['unidadOrigen', 'personaOrigen', 'derivaciones'])->first();

        $html = $caratulaService->renderCaratulaHtml($hr);
        $this->assertStringContainsString('EMPRESA DE APOYO A LA PRODUCCIÓN DE ALIMENTOS', $html);
        $this->assertStringContainsString('CARÁTULA OFICIAL DE HOJA DE RUTA', $html);
        $this->assertStringContainsString($hr->nro_hoja_ruta, $html);
        $this->assertStringContainsString('api.qrserver.com', $html);
    }

    public function test_verificacion_publica_de_documento(): void
    {
        $doc = Documento::where('estado', 'FIRMADO')->first();
        if (!$doc) return;

        $response = $this->getJson("/api/correspondencia/publico/verificar-documento/{$doc->codigo_verificacion}");
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('valido', true);
        $response->assertJsonPath('data.cite', $doc->cite);
    }

    public function test_ventanilla_unica_recepcion_externa(): void
    {
        $ventanilla = Ventanilla::first();
        $unidad = UnidadOrganizacional::first();

        $response = $this->withoutMiddleware()->postJson('/api/correspondencia/ventanillas/entrada', [
            'id_ventanilla' => $ventanilla ? $ventanilla->id : 1,
            'remitente_externo' => 'ASOCIACIÓN NACIONAL DE PRODUCTORES DE OLEAGINOSAS (ANAPO)',
            'asunto' => 'SOLICITUD DE COOPERACIÓN TÉCNICA Y PRECIOS DE SUSTENTACIÓN',
            'referencia' => 'CITE: ANAPO-PRE-092/2026',
            'prioridad' => 'ALTA',
            'nro_fojas' => 8,
            'nro_anexos' => 1,
            'id_unidad_destino' => $unidad->id,
            'proveido' => 'PASE A SUS EFECTOS',
            'dias_plazo' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('correspondencia.hojas_ruta', [
            'tipo_hr' => 'EXTERNA',
            'remitente_externo' => 'ASOCIACIÓN NACIONAL DE PRODUCTORES DE OLEAGINOSAS (ANAPO)',
        ]);
    }

    public function test_despacho_salida_y_etiquetas(): void
    {
        $hr = HojaRuta::first();

        // 1. Crear despacho
        $responseDesp = $this->withoutMiddleware()->postJson('/api/correspondencia/despachos', [
            'id_hoja_ruta' => $hr->id,
            'tipo_despacho' => 'COURIER_POSTAL',
            'nro_guia_despacho' => 'GUIA-BO-' . uniqid(),
            'destinatario_institucion' => 'MINISTERIO DE ECONOMIA Y FINANZAS PUBLICAS',
            'destinatario_ciudad' => 'La Paz',
        ]);
        $responseDesp->assertStatus(201);

        // 2. Crear y asignar etiqueta
        $nombreEtiq = 'ETIQ_' . uniqid();
        $responseEtiq = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas', [
            'nombre' => $nombreEtiq,
            'color' => '#D32F2F',
        ]);
        $responseEtiq->assertStatus(201);
        $idEtiq = $responseEtiq->json('data.id');

        $responseAsig = $this->withoutMiddleware()->postJson('/api/correspondencia/etiquetas/asignar', [
            'id_etiqueta' => $idEtiq,
            'id_hoja_ruta' => $hr->id,
        ]);
        $responseAsig->assertStatus(200);
        $this->assertDatabaseHas('correspondencia.etiquetas_participantes', [
            'id_etiqueta' => $idEtiq,
            'id_hoja_ruta' => $hr->id,
        ]);
    }

    public function test_solicitud_ciudadana_y_conversion_en_hoja_ruta(): void
    {
        $unidad = UnidadOrganizacional::first();

        // 1. Registro público
        $responsePub = $this->postJson('/api/correspondencia/publico/solicitud-ciudadana', [
            'solicitante_nombre' => 'MARIA DEL CARMEN MAMANI',
            'solicitante_ci_nit' => '8921033 LP',
            'solicitante_telefono' => '71543210',
            'solicitante_correo' => 'maria.mamani@gmail.com',
            'tipo_solicitud' => 'VENTA_DIRECTA_HARINA',
            'descripcion_solicitud' => 'Solicito compra directa de 50 quintales de harina de trigo subsidiada.',
        ]);
        $responsePub->assertStatus(201);
        $idSol = $responsePub->json('data.id');

        // 2. Admisión y conversión
        $responseConv = $this->withoutMiddleware()->postJson("/api/correspondencia/solicitudes-ciudadanas/{$idSol}/convertir-hoja-ruta", [
            'id_unidad_destino' => $unidad->id,
            'proveido' => 'PARA SU ATENCIÓN Y RESPUESTA',
            'dias_plazo' => 2,
        ]);
        $responseConv->assertStatus(200);
        $this->assertDatabaseHas('correspondencia.solicitudes_ciudadanas', [
            'id' => $idSol,
            'estado_solicitud' => 'HOJA_RUTA_GENERADA',
        ]);
    }
}
