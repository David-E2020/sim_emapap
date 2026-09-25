<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Abonado;
use App\Models\Comercial\Medidor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class AbonadoController extends Controller
{
    /**
     * Listado paginado de abonados con filtros reactivos.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $tipoBusqueda = (string) $request->input('tipo_busqueda', 'todos');
        $idZona = $request->input('id_zona');
        $idCategoria = $request->input('id_categoria');
        $estado = $request->input('estado_servicio');
        $soloMora = $request->boolean('solo_mora');

        $query = Abonado::with(['zona', 'calle', 'categoria', 'medidorActual'])
            ->orderBy('codigo');

        if (!empty($search)) {
            $criterio = trim((string) $search);
            $codigoPad = str_pad($criterio, 5, '0', STR_PAD_LEFT);

            if ($tipoBusqueda === 'codigo_abonado') {
                $query->where(function ($q) use ($criterio, $codigoPad) {
                    $q->where('codigo', $criterio)
                        ->orWhere('codigo', $codigoPad)
                        ->orWhere('codigo', 'like', "%{$criterio}%");
                });
            } elseif ($tipoBusqueda === 'carnet_nit') {
                $query->where(function ($q) use ($criterio) {
                    $q->where('numero_documento', $criterio)
                        ->orWhere('numero_documento', 'like', "%{$criterio}%");
                });
            } elseif ($tipoBusqueda === 'cliente') {
                $query->where('nombre_completo', 'ilike', "%{$criterio}%");
            } elseif ($tipoBusqueda === 'medidor') {
                $query->whereHas('medidorActual', fn($qm) => $qm->where('numero_serie', 'ilike', "%{$criterio}%"));
            } else {
                // Modo 'todos' los campos
                $query->where(function ($q) use ($criterio, $codigoPad) {
                    $q->where('codigo', $criterio)
                        ->orWhere('codigo', $codigoPad)
                        ->orWhere('codigo', 'like', "%{$criterio}%")
                        ->orWhere('nombre_completo', 'ilike', "%{$criterio}%")
                        ->orWhere('numero_documento', 'like', "%{$criterio}%")
                        ->orWhereHas('medidorActual', fn($qm) => $qm->where('numero_serie', 'ilike', "%{$criterio}%"));
                });
            }
        }

        if (!empty($idZona)) {
            $query->where('id_zona', $idZona);
        }

        if (!empty($idCategoria)) {
            $query->where('id_categoria', $idCategoria);
        }

        if (!empty($estado) && $estado !== 'TODOS') {
            if ($estado === 'EN_MORA') {
                $query->where('meses_mora', '>=', 2);
            } elseif (in_array($estado, ['CORTE', 'CORTADO'])) {
                $query->whereIn('estado_servicio', ['CORTE', 'CORTADO']);
            } else {
                $query->where('estado_servicio', $estado);
            }
        }

        if ($soloMora) {
            $query->where('meses_mora', '>=', 2);
        }

        $paginator = $query->paginate($perPage);

        // Resumen global de métricas operativas del padrón completo
        $resumen = [
            'total_abonados' => Abonado::count(),
            'servicios_activos' => Abonado::where('estado_servicio', 'ACTIVO')->count(),
            'en_mora' => Abonado::where('meses_mora', '>=', 2)->count(),
            'servicios_cortados' => Abonado::whereIn('estado_servicio', ['CORTE', 'CORTADO'])->count(),
            'suspendidos' => Abonado::where('estado_servicio', 'SUSPENDIDO')->count(),
            'permisos' => Abonado::where('estado_servicio', 'PERMISO')->count(),
            'bajas' => Abonado::where('estado_servicio', 'BAJA')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'resumen' => $resumen,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Ficha detallada del abonado con historial de lecturas y convenios.
     */
    public function show(int $id): JsonResponse
    {
        $abonado = Abonado::with([
            'zona',
            'calle',
            'categoria',
            'medidorActual',
            'lecturas' => fn($q) => $q->with(['periodo', 'facturaSiat'])->orderByDesc('id')->limit(48),
            'convenios' => fn($q) => $q->with('cuotas')->orderByDesc('id'),
            'ordenesTrabajo' => fn($q) => $q->orderByDesc('id')->limit(10),
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $abonado,
        ], Response::HTTP_OK);
    }

    /**
     * Registro de nuevo abonado.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tipo_persona' => 'required|in:NATURAL,JURIDICA',
            'primer_apellido' => 'nullable|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'nombres' => 'nullable|string|max:60',
            'nombre_completo' => 'required|string|max:150',
            'numero_documento' => 'nullable|string|max:25',
            'complemento' => 'nullable|string|max:5',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|string|max:100',
            'persona_contacto' => 'nullable|string|max:150',
            'id_zona' => 'required|integer|exists:pgsql.comercial.zonas,id',
            'id_calle' => 'nullable|integer|exists:pgsql.comercial.calles,id',
            'numero_vivienda' => 'nullable|string|max:20',
            'edificio' => 'nullable|string|max:30',
            'departamento' => 'nullable|string|max:15',
            'referencia_direccion' => 'nullable|string',
            'id_categoria' => 'required|integer|exists:pgsql.comercial.categorias_tarifarias,id',
            'tiene_alcantarillado' => 'boolean',
            'es_tercera_edad' => 'boolean',
            'tiene_medidor' => 'boolean',
            'id_medidor_actual' => 'nullable|integer|exists:pgsql.comercial.medidores,id',
            'fecha_ingreso' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Generar correlativo de código de socio de 5 dígitos sin colisiones
        $maxCodigo = (int) (Abonado::selectRaw("COALESCE(MAX(CAST(NULLIF(regexp_replace(codigo, '[^0-9]', '', 'g'), '') AS INTEGER)), 0) as max_cod")->value('max_cod') ?? 0);
        $siguienteNum = max($maxCodigo + 1, 1);
        $codigoNuevo = sprintf('%05d', $siguienteNum);
        while (Abonado::where('codigo', $codigoNuevo)->exists()) {
            $siguienteNum++;
            $codigoNuevo = sprintf('%05d', $siguienteNum);
        }

        $nroMedidor = trim((string) $request->input('numero_medidor', ''));
        $idMedidor = null;
        if (!empty($nroMedidor)) {
            $medidor = Medidor::firstOrCreate(
                ['numero_serie' => $nroMedidor],
                [
                    'marca' => 'Sensus',
                    'diametro' => '1/2"',
                    'estado' => 'OPERATIVO',
                ]
            );
            $idMedidor = $medidor->id;
        }

        $abonado = Abonado::create(array_merge($request->except('numero_medidor'), [
            'codigo' => $codigoNuevo,
            'estado_servicio' => $request->input('estado_servicio', 'ACTIVO'),
            'id_medidor_actual' => $idMedidor,
            'tiene_medidor' => ($idMedidor !== null || $request->boolean('tiene_medidor')),
            'fecha_ingreso' => $request->input('fecha_ingreso', date('Y-m-d')),
            'saldo_deuda' => 0.00,
            'meses_mora' => 0,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Abonado registrado exitosamente con código ' . $codigoNuevo,
            'data' => $abonado->load(['zona', 'calle', 'categoria', 'medidorActual']),
        ], Response::HTTP_CREATED);
    }

    /**
     * Actualización de datos del abonado.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $abonado = Abonado::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'tipo_persona' => 'sometimes|in:NATURAL,JURIDICA',
            'primer_apellido' => 'nullable|string|max:50',
            'segundo_apellido' => 'nullable|string|max:50',
            'nombres' => 'nullable|string|max:60',
            'nombre_completo' => 'sometimes|string|max:150',
            'numero_documento' => 'nullable|string|max:25',
            'complemento' => 'nullable|string|max:5',
            'telefono' => 'nullable|string|max:20',
            'celular' => 'nullable|string|max:20',
            'email' => 'nullable|string|max:100',
            'persona_contacto' => 'nullable|string|max:150',
            'id_zona' => 'sometimes|integer|exists:pgsql.comercial.zonas,id',
            'id_calle' => 'nullable|integer|exists:pgsql.comercial.calles,id',
            'numero_vivienda' => 'nullable|string|max:20',
            'edificio' => 'nullable|string|max:30',
            'departamento' => 'nullable|string|max:15',
            'referencia_direccion' => 'nullable|string',
            'id_categoria' => 'sometimes|integer|exists:pgsql.comercial.categorias_tarifarias,id',
            'tiene_alcantarillado' => 'boolean',
            'es_tercera_edad' => 'boolean',
            'estado_servicio' => 'sometimes|string',
            'numero_medidor' => 'nullable|string|max:50',
            'fecha_ingreso' => 'nullable|date',
            'observaciones' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $abonado->update($request->except('numero_medidor'));

        if ($request->has('numero_medidor')) {
            $nroMedidor = trim((string) $request->input('numero_medidor', ''));
            if (!empty($nroMedidor)) {
                if (!$abonado->medidorActual || $abonado->medidorActual->numero_serie !== $nroMedidor) {
                    $medidor = Medidor::firstOrCreate(
                        ['numero_serie' => $nroMedidor],
                        [
                            'marca' => 'Sensus',
                            'diametro' => '1/2"',
                            'estado' => 'OPERATIVO',
                        ]
                    );
                    $abonado->update([
                        'id_medidor_actual' => $medidor->id,
                        'tiene_medidor' => true,
                    ]);
                }
            } else {
                if ($request->has('tiene_medidor') && !$request->boolean('tiene_medidor')) {
                    $abonado->update([
                        'id_medidor_actual' => null,
                        'tiene_medidor' => false,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Datos del abonado actualizados correctamente',
            'data' => $abonado->fresh(['zona', 'calle', 'categoria', 'medidorActual']),
        ], Response::HTTP_OK);
    }

    /**
     * Registro de cambio y reemplazo de medidor de agua.
     */
    public function cambiarMedidor(Request $request, int $id): JsonResponse
    {
        $abonado = Abonado::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'numero_serie_nuevo' => 'required|string|max:50',
            'marca' => 'nullable|string|max:50',
            'diametro' => 'nullable|string|max:20',
            'lectura_final_anterior' => 'required|numeric|min:0',
            'lectura_inicial_nuevo' => 'required|numeric|min:0',
            'motivo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 1. Marcar medidor anterior como CAMBIADO
        if ($abonado->medidorActual) {
            $abonado->medidorActual->update(['estado' => 'CAMBIADO']);
        }

        // 2. Crear o asignar nuevo medidor
        $nuevoMedidor = \App\Models\Comercial\Medidor::firstOrCreate(
            ['numero_serie' => trim((string) $request->input('numero_serie_nuevo'))],
            [
                'marca' => $request->input('marca', 'Sensus'),
                'diametro' => $request->input('diametro', '1/2"'),
                'lectura_inicial' => (float) $request->input('lectura_inicial_nuevo'),
                'estado' => 'OPERATIVO',
            ]
        );

        // 3. Asignar nuevo medidor al abonado
        $abonado->update([
            'tiene_medidor' => true,
            'id_medidor_actual' => $nuevoMedidor->id,
        ]);

        // 4. Registrar Orden de Trabajo
        \App\Models\Comercial\OrdenTrabajo::create([
            'numero_orden' => 'CAMB-' . date('Y') . '-' . sprintf('%05d', \App\Models\Comercial\OrdenTrabajo::count() + 1),
            'id_abonado' => $abonado->id,
            'tipo_orden' => 'CAMBIO_MEDIDOR',
            'motivo' => $request->input('motivo'),
            'fecha_programada' => date('Y-m-d'),
            'fecha_ejecucion' => now(),
            'lectura_en_corte' => (float) $request->input('lectura_final_anterior'),
            'informe_tecnico' => "Cambio por nuevo medidor serie {$nuevoMedidor->numero_serie} con lectura inicial {$nuevoMedidor->lectura_inicial} m³",
            'estado' => 'EJECUTADO',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Medidor reemplazado y asignado exitosamente.',
            'data' => $abonado->fresh(['medidorActual']),
        ], Response::HTTP_OK);
    }

    /**
     * Registro de Baja Definitiva de Servicio de Agua.
     */
    public function darDeBaja(Request $request, int $id): JsonResponse
    {
        $abonado = Abonado::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'motivo' => 'required|string',
            'lectura_retiro' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $abonado->update([
            'estado_servicio' => 'BAJA',
        ]);

        if ($abonado->medidorActual) {
            $abonado->medidorActual->update(['estado' => 'BAJA']);
        }

        // Registrar orden de baja
        \App\Models\Comercial\OrdenTrabajo::create([
            'numero_orden' => 'BAJA-' . date('Y') . '-' . sprintf('%05d', \App\Models\Comercial\OrdenTrabajo::count() + 1),
            'id_abonado' => $abonado->id,
            'tipo_orden' => 'BAJA_DEFINITIVA',
            'motivo' => $request->input('motivo'),
            'fecha_programada' => date('Y-m-d'),
            'fecha_ejecucion' => now(),
            'lectura_en_corte' => $request->input('lectura_retiro') ? (float) $request->input('lectura_retiro') : null,
            'estado' => 'EJECUTADO',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Servicio del abonado {$abonado->codigo} dado de baja definitivamente.",
            'data' => $abonado->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Descarga el Extracto Histórico de Cuenta en PDF (equivalente al extracto.frx de FoxPro).
     */
    public function descargarExtractoPdf(
        int $id,
        \App\Services\Comercial\DocumentoComercialPdfService $pdfService
    ): \Illuminate\Http\Response {
        $abonado = Abonado::findOrFail($id);
        $pdf = $pdfService->generarExtractoHistoricoPdf($abonado);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Extracto_Cuenta_{$abonado->codigo}.pdf\"",
        ]);
    }
}
