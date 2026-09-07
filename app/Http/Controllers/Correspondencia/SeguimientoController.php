<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\HojaRuta;
use App\Services\Correspondencia\DerivacionWorkflowService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SeguimientoController extends Controller
{
    public function __construct(
        protected readonly DerivacionWorkflowService $workflowService
    ) {}

    /**
     * Obtener el Timeline cronológico, árbol genealógico y anexos de una Hoja de Ruta (LONDRA EXACT ENGINE)
     */
    public function timeline(int|string $id): JsonResponse
    {
        $query = HojaRuta::with([
            'unidadOrigen',
            'personaOrigen',
            'cargoOrigen',
            'entidadExterna',
            'documentos.firmasAprobaciones.persona',
            'derivaciones.funcionarioOrigen',
            'derivaciones.cargoOrigen',
            'derivaciones.unidadOrigen',
            'derivaciones.funcionarioDestino',
            'derivaciones.cargoDestino',
            'derivaciones.unidadDestino',
            'agrupaciones.hojaRutaAnexada',
            'archivosAdjuntos',
        ]);

        if (is_numeric($id)) {
            $hojaRuta = $query->find((int) $id);
        } else {
            $hojaRuta = $query->where('nro_hoja_ruta', trim((string) $id))->first();
        }

        if (! $hojaRuta) {
            return response()->json([
                'success' => false,
                'message' => 'Expediente no encontrado.',
            ], Response::HTTP_NOT_FOUND);
        }

        $eventos = [];

        // 1. Evento de creación
        $eventos[] = [
            'tipo' => 'CREACION',
            'titulo' => 'Ingreso de Hoja de Ruta',
            'subtitulo' => $hojaRuta->nro_hoja_ruta,
            'actor' => $hojaRuta->personaOrigen ? $hojaRuta->personaOrigen->nombre_completo : ($hojaRuta->remitente_externo ?: 'Ventanilla'),
            'cargo' => $hojaRuta->cargoOrigen ? $hojaRuta->cargoOrigen->nombre : 'Remitente',
            'unidad' => $hojaRuta->unidadOrigen ? $hojaRuta->unidadOrigen->nombre : 'Origen',
            'fecha' => Carbon::parse($hojaRuta->fecha_solicitud)->toIso8601String(),
            'fecha_formateada' => Carbon::parse($hojaRuta->fecha_solicitud)->format('d/m/Y H:i'),
            'icono' => 'mdi-file-plus-outline',
            'color' => 'primary',
            'detalles' => [
                'asunto' => $hojaRuta->asunto,
                'prioridad' => $hojaRuta->prioridad,
                'fojas' => $hojaRuta->nro_fojas,
                'anexos' => $hojaRuta->nro_anexos,
            ],
        ];

        // 2. Eventos de cada derivación
        foreach ($hojaRuta->derivaciones->sortBy('id') as $der) {
            $semaforo = $this->workflowService->calcularSemaforo($der);

            $color = match ($der->estado_derivacion) {
                'DEVUELTO_OBSERVADO' => 'orange',
                'ANULADO' => 'red',
                'RECIBIDO' => 'teal',
                'PROCESADO' => 'green',
                default => 'blue',
            };

            $eventos[] = [
                'tipo' => 'DERIVACION',
                'id_derivacion' => $der->id,
                'titulo' => "Derivación: {$der->proveido}",
                'subtitulo' => $der->estado_derivacion,
                'de' => $der->funcionarioOrigen ? $der->funcionarioOrigen->nombre_completo : 'N/A',
                'de_unidad' => $der->unidadOrigen ? $der->unidadOrigen->nombre : 'N/A',
                'a' => $der->funcionarioDestino ? $der->funcionarioDestino->nombre_completo : 'N/A',
                'a_cargo' => $der->cargoDestino ? $der->cargoDestino->nombre : 'N/A',
                'a_unidad' => $der->unidadDestino ? $der->unidadDestino->nombre : 'N/A',
                'instruccion' => $der->instruccion_detalle,
                'fecha' => Carbon::parse($der->fecha_derivacion)->toIso8601String(),
                'fecha_formateada' => Carbon::parse($der->fecha_derivacion)->format('d/m/Y H:i'),
                'fecha_recepcion' => $der->fecha_recepcion ? Carbon::parse($der->fecha_recepcion)->format('d/m/Y H:i') : null,
                'fecha_atencion' => $der->fecha_atencion ? Carbon::parse($der->fecha_atencion)->format('d/m/Y H:i') : null,
                'icono' => match ($der->estado_derivacion) {
                    'RECIBIDO' => 'mdi-inbox-check-outline',
                    'DEVUELTO_OBSERVADO' => 'mdi-undo-variant',
                    'ANULADO' => 'mdi-cancel',
                    default => 'mdi-send-clock-outline',
                },
                'color' => $color,
                'semaforo' => $semaforo,
                'es_copia' => (bool) $der->es_copia,
                'mpath' => $der->mpath,
            ];
        }

        // 3. Evento de conclusión si aplica
        if ($hojaRuta->estado === 'CERRADO' && $hojaRuta->fecha_cierre) {
            $eventos[] = [
                'tipo' => 'CONCLUIDO',
                'titulo' => 'Expediente Concluido y Archivado',
                'subtitulo' => $hojaRuta->motivo_cierre,
                'fecha' => Carbon::parse($hojaRuta->fecha_cierre)->toIso8601String(),
                'fecha_formateada' => Carbon::parse($hojaRuta->fecha_cierre)->format('d/m/Y H:i'),
                'icono' => 'mdi-archive-check',
                'color' => 'teal',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'hoja_ruta' => $hojaRuta,
                'timeline' => $eventos,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Consulta pública de seguimiento para ciudadanos y usuarios externos
     */
    public function publico(string $codigo): JsonResponse
    {
        return $this->timeline($codigo);
    }
}
