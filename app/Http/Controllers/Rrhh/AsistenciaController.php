<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Asistencia;
use App\Models\Rrhh\Biometrico;
use App\Models\Rrhh\Persona;
use App\Services\Biometrics\ZkBiometricService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AsistenciaController extends Controller
{
    public function __construct(
        private readonly ZkBiometricService $biometricService
    ) {}

    /**
     * Lista todos los relojes biométricos registrados y su estado de conectividad en red local.
     */
    public function listarBiometricos(): JsonResponse
    {
        $biometricos = Biometrico::where('_estado', 'ACTIVO')
            ->orderBy('id')
            ->get()
            ->map(function ($b) {
                $isOnline = $this->biometricService->pingDevice($b->url, (int) $b->puerto, 0.5);
                $b->is_online = $isOnline;
                $b->total_marcaciones = DB::table('rrhh.marcaciones')->where('id_biometrico', $b->id)->count();
                $b->ultima_sincronizacion = DB::table('rrhh.sincronizaciones')
                    ->where('id_biometrico', $b->id)
                    ->latest('fecha_sincronizacion')
                    ->value('fecha_sincronizacion');

                return $b;
            });

        return response()->json([
            'success' => true,
            'data' => $biometricos,
            'totales' => [
                'total' => $biometricos->count(),
                'online' => $biometricos->where('is_online', true)->count(),
                'offline' => $biometricos->where('is_online', false)->count(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Registra un nuevo reloj biométrico en red local.
     */
    public function storeBiometrico(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'url' => 'required|string|max:100',
            'puerto' => 'required|integer|min:1|max:65535',
            'modelo' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:200',
            'tipo' => 'nullable|string|max:50',
        ]);

        $bio = Biometrico::create([
            'nombre' => $validated['nombre'],
            'url' => trim($validated['url']),
            'puerto' => (int) $validated['puerto'],
            'modelo' => $validated['modelo'] ?? 'K40',
            'ubicacion' => $validated['ubicacion'] ?? 'Oficina Principal',
            'tipo' => $validated['tipo'] ?? 'ZKTeco Ethernet',
            '_estado' => 'ACTIVO',
            '_transaccion' => 'CREAR',
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Reloj biométrico '{$bio->nombre}' registrado correctamente.",
            'data' => $bio,
        ], Response::HTTP_CREATED);
    }

    /**
     * Actualiza la configuración de un reloj biométrico.
     */
    public function updateBiometrico(Request $request, int $id): JsonResponse
    {
        $bio = Biometrico::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'url' => 'required|string|max:100',
            'puerto' => 'required|integer|min:1|max:65535',
            'modelo' => 'nullable|string|max:50',
            'ubicacion' => 'nullable|string|max:200',
            'tipo' => 'nullable|string|max:50',
        ]);

        $bio->update([
            'nombre' => $validated['nombre'],
            'url' => trim($validated['url']),
            'puerto' => (int) $validated['puerto'],
            'modelo' => $validated['modelo'] ?? $bio->modelo,
            'ubicacion' => $validated['ubicacion'] ?? $bio->ubicacion,
            'tipo' => $validated['tipo'] ?? $bio->tipo,
            '_transaccion' => 'MODIFICAR',
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Reloj biométrico '{$bio->nombre}' actualizado correctamente.",
            'data' => $bio,
        ], Response::HTTP_OK);
    }

    /**
     * Desactiva / Elimina un reloj biométrico.
     */
    public function eliminarBiometrico(int $id): JsonResponse
    {
        $bio = Biometrico::findOrFail($id);
        $bio->update([
            '_estado' => 'INACTIVO',
            '_transaccion' => 'ELIMINAR',
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Reloj biométrico '{$bio->nombre}' deshabilitado exitosamente.",
        ], Response::HTTP_OK);
    }

    /**
     * Prueba de conectividad rápida a un reloj específico.
     */
    public function probarConexion(int $id): JsonResponse
    {
        $biometrico = Biometrico::findOrFail($id);
        $isOnline = $this->biometricService->pingDevice($biometrico->url, (int) $biometrico->puerto, 1.5);

        return response()->json([
            'success' => true,
            'online' => $isOnline,
            'message' => $isOnline
                ? "El reloj biométrico \"{$biometrico->nombre}\" ({$biometrico->url}) está en línea."
                : "No se puede alcanzar el reloj biométrico en {$biometrico->url}:{$biometrico->puerto}.",
        ], Response::HTTP_OK);
    }

    /**
     * Prueba ping masivo a todos los dispositivos registrados.
     */
    public function probarTodos(): JsonResponse
    {
        $biometricos = Biometrico::where('_estado', 'ACTIVO')->get();
        $online = 0;
        $offline = 0;
        $resultados = [];

        foreach ($biometricos as $b) {
            $status = $this->biometricService->pingDevice($b->url, (int) $b->puerto, 1.0);
            if ($status) {
                $online++;
            } else {
                $offline++;
            }
            $resultados[] = [
                'id' => $b->id,
                'nombre' => $b->nombre,
                'url' => $b->url,
                'is_online' => $status,
            ];
        }

        return response()->json([
            'success' => true,
            'online_count' => $online,
            'offline_count' => $offline,
            'data' => $resultados,
        ], Response::HTTP_OK);
    }

    /**
     * Sincroniza marcaciones de un reloj específico.
     */
    public function sincronizar(int $id): JsonResponse
    {
        $biometrico = Biometrico::findOrFail($id);

        try {
            $resultado = $this->biometricService->syncBiometricoToDatabase($biometrico);

            return response()->json([
                'success' => true,
                'message' => "Sincronización completada: {$resultado['insertadas']} nuevas marcaciones registradas ({$resultado['omitidas']} ya existentes).",
                'data' => $resultado,
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al sincronizar reloj biometrico', ['id' => $id, 'exception' => $ex->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Fallo al sincronizar con el reloj: '.$ex->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sincronización en lote de todos los relojes que estén en línea.
     */
    public function sincronizarTodos(): JsonResponse
    {
        $biometricos = Biometrico::where('_estado', 'ACTIVO')->get();
        $insertadasTotal = 0;
        $omitidasTotal = 0;
        $errores = [];

        foreach ($biometricos as $b) {
            if ($this->biometricService->pingDevice($b->url, (int) $b->puerto, 1.0)) {
                try {
                    $res = $this->biometricService->syncBiometricoToDatabase($b);
                    $insertadasTotal += $res['insertadas'] ?? 0;
                    $omitidasTotal += $res['omitidas'] ?? 0;
                } catch (\Throwable $ex) {
                    $errores[] = "{$b->nombre}: " . $ex->getMessage();
                }
            } else {
                $errores[] = "{$b->nombre}: fuera de línea";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Sincronización masiva finalizada. {$insertadasTotal} marcaciones nuevas insertadas.",
            'insertadas_total' => $insertadasTotal,
            'omitidas_total' => $omitidasTotal,
            'errores' => $errores,
        ], Response::HTTP_OK);
    }

    /**
     * Consulta inteligente de asistencias (Institucional para RRHH / Personal para funcionarios).
     */
    public function listarAsistencias(Request $request): JsonResponse
    {
        $user = auth()->user();
        $esAdminOEncargado = false;

        if ($user) {
            if ($user->usr_usuario === 'admin' || $user->id === 1) {
                $esAdminOEncargado = true;
            } elseif (
                $user->can('rrhh.asistencias.ver_todos') ||
                $user->can('rrhh.asistencias.administrar') ||
                $user->hasRole(['Admin', 'Administrador', 'RRHH', 'Encargado RRHH'])
            ) {
                $esAdminOEncargado = true;
            }
        } else {
            // Entorno local o token sin usuario estricto
            $esAdminOEncargado = true;
        }

        $query = Asistencia::with([
            'persona.asignacionesPuestos.puesto.unidadOrganizacional',
        ]);

        $tipoVista = $request->query('tipo_vista', $esAdminOEncargado ? 'institucional' : 'personal');

        // SEGURIDAD: Si no es Admin/RRHH, forzar obligatoriamente a ver solo sus propios registros
        if (!$esAdminOEncargado) {
            $personaId = $user?->usr_externo_id;
            if (!$personaId) {
                return response()->json([
                    'success' => true,
                    'es_admin' => false,
                    'data' => [],
                    'totales' => [
                        'total_registros' => 0,
                        'presentes' => 0,
                        'atrasos' => 0,
                        'faltas' => 0,
                        'total_minutos_atraso' => 0,
                        'refrigerios_habilitados' => 0,
                        'monto_refrigerio_bs' => 0,
                    ],
                    'message' => 'El usuario no tiene una ficha de personal vinculada.',
                ], Response::HTTP_OK);
            }
            $query->where('id_persona', $personaId);
        } else {
            // Si es Admin y filtró por un funcionario específico
            if ($request->filled('id_persona')) {
                $query->where('id_persona', (int) $request->query('id_persona'));
            }
        }

        // Filtros temporales
        $fecha = $request->query('fecha');
        $mes = $request->query('mes');
        $anio = $request->query('anio', date('Y'));

        if ($fecha) {
            $query->where('fecha', $fecha);
        } elseif ($mes) {
            $query->whereYear('fecha', $anio)->whereMonth('fecha', $mes);
        } else {
            // Por defecto: si es vista diaria o no especificó mes, día de hoy
            if ($tipoVista === 'diario' || ($esAdminOEncargado && !$mes)) {
                $fechaHoy = now()->format('Y-m-d');
                $query->where('fecha', $fechaHoy);
                $fecha = $fechaHoy;
            } else {
                // Mes actual
                $mesActual = (int) date('m');
                $query->whereYear('fecha', $anio)->whereMonth('fecha', $mesActual);
                $mes = $mesActual;
            }
        }

        $rawAsistencias = $query->orderBy('fecha', 'desc')->orderBy('id_persona')->get();

        // Mapear y estandarizar datos para el frontend
        $data = $rawAsistencias->map(function ($item) {
            $persona = $item->persona;
            $puesto = $persona?->asignacionesPuestos?->first()?->puesto;
            $unidad = $puesto?->unidadOrganizacional?->nombre ?? 'EMAPA Central';

            $minAtraso = (int) ($item->minutos_de_atraso_primer_periodo ?? 0) + (int) ($item->minutos_de_atraso_segundo_periodo ?? 0);

            // Determinar estado amigable
            $estado = 'PRESENTE';
            if ($minAtraso > 0) {
                $estado = 'ATRASO';
            }
            if (!$item->entrada_primer_periodo && !$item->entrada_segundo_periodo) {
                $estado = 'FALTA';
            }

            // Calcular horas trabajadas estimadas
            $horasTrabajadas = 8.0;
            if ($item->entrada_primer_periodo && $item->salida_primer_periodo) {
                $e1 = Carbon::parse($item->entrada_primer_periodo);
                $s1 = Carbon::parse($item->salida_primer_periodo);
                $horasTrabajadas = round($s1->diffInMinutes($e1) / 60.0, 1);
            }

            return [
                'id' => $item->id,
                'id_persona' => $item->id_persona,
                'fecha' => $item->fecha ? Carbon::parse($item->fecha)->format('Y-m-d') : '-',
                'dia' => $item->dia ?: ($item->fecha ? Carbon::parse($item->fecha)->locale('es')->dayName : '-'),
                'funcionario' => $persona ? $persona->nombre_completo : 'Funcionario',
                'ci' => $persona ? $persona->nro_documento : '-',
                'cargo' => $puesto ? $puesto->nombre : 'Funcionario',
                'unidad' => $unidad,
                'entrada_1' => $item->entrada_primer_periodo ? substr((string)$item->entrada_primer_periodo, 0, 5) : '-',
                'salida_1' => $item->salida_primer_periodo ? substr((string)$item->salida_primer_periodo, 0, 5) : '-',
                'entrada_2' => $item->entrada_segundo_periodo ? substr((string)$item->entrada_segundo_periodo, 0, 5) : '-',
                'salida_2' => $item->salida_segundo_periodo ? substr((string)$item->salida_segundo_periodo, 0, 5) : '-',
                'horas_trabajadas' => $horasTrabajadas,
                'minutos_atraso' => $minAtraso,
                'estado' => $estado,
                'merece_refrigerio' => (bool) $item->merece_refrigerio,
                'refrigerio_bs' => $item->merece_refrigerio ? 18.00 : 0.00,
            ];
        });

        // Totales y KPIs
        $totalDias = $data->count();
        $presentes = $data->whereIn('estado', ['PRESENTE', 'ATRASO'])->count();
        $atrasos = $data->where('minutos_atraso', '>', 0)->count();
        $faltas = $data->where('estado', 'FALTA')->count();
        $totalMinAtraso = $data->sum('minutos_atraso');
        $refrigeriosCount = $data->where('merece_refrigerio', true)->count();
        $montoRefrigerioBs = round($refrigeriosCount * 18.0, 2);

        return response()->json([
            'success' => true,
            'es_admin' => $esAdminOEncargado,
            'tipo_vista' => $tipoVista,
            'fecha' => $fecha,
            'mes' => $mes,
            'anio' => $anio,
            'totales' => [
                'total_registros' => $totalDias,
                'presentes' => $presentes,
                'atrasos' => $atrasos,
                'faltas' => $faltas,
                'total_minutos_atraso' => $totalMinAtraso,
                'refrigerios_habilitados' => $refrigeriosCount,
                'monto_refrigerio_bs' => $montoRefrigerioBs,
            ],
            'data' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Procesa y calcula la asistencia diaria de los funcionarios para una fecha dada.
     */
    public function calcularAsistencia(Request $request): JsonResponse
    {
        $fecha = $request->input('fecha', now()->format('Y-m-d'));

        try {
            $totalProcesados = $this->biometricService->calculateDailyAttendanceForDate($fecha);

            return response()->json([
                'success' => true,
                'message' => "Cálculo de asistencia completado para {$fecha}: {$totalProcesados} funcionarios procesados.",
            ], Response::HTTP_OK);
        } catch (\Throwable $ex) {
            Log::error('Error al calcular asistencia', ['fecha' => $fecha, 'exception' => $ex->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Error al calcular asistencia.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Marca o regulariza la asistencia de uno, dos o muchos funcionarios para una fecha específica.
     */
    public function marcarAsistenciaMasiva(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'personas_ids' => 'required|array|min:1',
            'fecha' => 'required|date',
            'estado' => 'required|string|in:PRESENTE,ATRASO,FALTA,PERMISO,COMISION',
            'entrada_1' => 'nullable|string',
            'salida_1' => 'nullable|string',
            'entrada_2' => 'nullable|string',
            'salida_2' => 'nullable|string',
            'minutos_atraso' => 'nullable|integer|min:0',
            'merece_refrigerio' => 'nullable|boolean',
        ]);

        $fecha = $validated['fecha'];
        $estado = $validated['estado'];
        $entrada1 = $validated['entrada_1'] ?? ($estado === 'FALTA' ? null : '08:30');
        $salida1 = $validated['salida_1'] ?? ($estado === 'FALTA' ? null : '16:30');
        $entrada2 = $validated['entrada_2'] ?? null;
        $salida2 = $validated['salida_2'] ?? null;
        $minAtraso = (int) ($validated['minutos_atraso'] ?? ($estado === 'ATRASO' ? 15 : 0));
        $refrigerio = $validated['merece_refrigerio'] ?? in_array($estado, ['PRESENTE', 'ATRASO']);
        $diaSemana = Carbon::parse($fecha)->locale('es')->dayName;

        $procesados = 0;
        DB::transaction(function () use ($validated, $fecha, $entrada1, $salida1, $entrada2, $salida2, $minAtraso, $refrigerio, $diaSemana, &$procesados) {
            foreach ($validated['personas_ids'] as $idPersona) {
                Asistencia::updateOrCreate(
                    [
                        'fecha' => $fecha,
                        'id_persona' => (int) $idPersona,
                    ],
                    [
                        'entrada_primer_periodo' => $entrada1,
                        'salida_primer_periodo' => $salida1,
                        'entrada_segundo_periodo' => $entrada2,
                        'salida_segundo_periodo' => $salida2,
                        'minutos_de_atraso_primer_periodo' => $minAtraso,
                        'merece_refrigerio' => $refrigerio,
                        'dia' => $diaSemana,
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'ASIST_MASIVA',
                        '_usuario_creacion' => auth()->id() ?? 1,
                        '_fecha_creacion' => now(),
                    ]
                );
                $procesados++;
            }
        });

        return response()->json([
            'success' => true,
            'message' => "Asistencia registrada correctamente para {$procesados} funcionarios en fecha {$fecha}.",
            'procesados' => $procesados,
        ], Response::HTTP_OK);
    }

    /**
     * Carga o regulariza la asistencia mensual completa (basada en libro de firmas físico / Excel).
     */
    public function marcarMesCompleto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer|min:2020|max:2035',
            'personas_ids' => 'nullable|array',
            'hora_entrada' => 'nullable|string',
            'hora_salida' => 'nullable|string',
        ]);

        $mes = (int) $validated['mes'];
        $anio = (int) $validated['anio'];
        $horaEntrada = $validated['hora_entrada'] ?? '08:30';
        $horaSalida = $validated['hora_salida'] ?? '16:30';

        // Obtener funcionarios a procesar (si no especifica, todos los activos)
        $personasQuery = Persona::where('_estado', 'ACTIVO');
        if (!empty($validated['personas_ids'])) {
            $personasQuery->whereIn('id', $validated['personas_ids']);
        }
        $personas = $personasQuery->get();

        if ($personas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron funcionarios activos para registrar asistencia.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Obtener días del mes (días hábiles lunes a viernes)
        $inicioMes = Carbon::createFromDate($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::createFromDate($anio, $mes, 1)->endOfMonth();
        $fechasHabiles = [];

        for ($date = $inicioMes->copy(); $date->lte($finMes); $date->addDay()) {
            if ($date->isWeekday()) {
                $fechasHabiles[] = [
                    'fecha' => $date->format('Y-m-d'),
                    'dia' => $date->locale('es')->dayName,
                ];
            }
        }

        $totalRegistros = 0;
        DB::transaction(function () use ($personas, $fechasHabiles, $horaEntrada, $horaSalida, &$totalRegistros) {
            foreach ($personas as $persona) {
                foreach ($fechasHabiles as $d) {
                    Asistencia::updateOrCreate(
                        [
                            'fecha' => $d['fecha'],
                            'id_persona' => $persona->id,
                        ],
                        [
                            'entrada_primer_periodo' => $horaEntrada,
                            'salida_primer_periodo' => $horaSalida,
                            'minutos_de_atraso_primer_periodo' => 0,
                            'merece_refrigerio' => true,
                            'dia' => $d['dia'],
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'ASIST_LIBRO',
                            '_usuario_creacion' => auth()->id() ?? 1,
                            '_fecha_creacion' => now(),
                        ]
                    );
                    $totalRegistros++;
                }
            }
        });

        $nombreMes = Carbon::createFromDate($anio, $mes, 1)->locale('es')->monthName;
        return response()->json([
            'success' => true,
            'message' => "Asistencia mensual de {$nombreMes} {$anio} regularizada exitosamente: {$personas->count()} funcionarios ({$totalRegistros} marcaciones registradas).",
            'total_personas' => $personas->count(),
            'total_registros' => $totalRegistros,
            'dias_habiles' => count($fechasHabiles),
        ], Response::HTTP_OK);
    }

    /**
     * Verifica si existen registros de asistencia procesados para un mes y gestión dados.
     */
    public function verificarAsistenciaMes(Request $request): JsonResponse
    {
        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));

        $totalRegistros = Asistencia::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->count();

        $funcionariosCount = Asistencia::whereYear('fecha', $anio)
            ->whereMonth('fecha', $mes)
            ->distinct('id_persona')
            ->count('id_persona');

        $tieneAsistencia = $totalRegistros > 0;

        return response()->json([
            'success' => true,
            'mes' => $mes,
            'anio' => $anio,
            'tiene_asistencia' => $tieneAsistencia,
            'total_registros' => $totalRegistros,
            'total_funcionarios' => $funcionariosCount,
            'alerta' => !$tieneAsistencia
                ? "No se registran asistencias procesadas para el período {$mes}/{$anio}. La planilla se simulará con los días base, pero se recomienda registrar la asistencia mensual previamente."
                : null,
        ], Response::HTTP_OK);
    }

    /**
     * Genera el reporte oficial de asistencias en formato PDF de alta resolución.
     */
    public function exportarPdf(Request $request)
    {
        $res = $this->listarAsistencias($request)->getData(true);
        $data = $res['data'] ?? [];
        $totales = $res['totales'] ?? [];

        $modoVista = $request->query('tipo', $request->query('modo', 'diario'));
        $fecha = $request->query('fecha', now()->format('Y-m-d'));
        $mes = (int) $request->query('mes', date('m'));
        $anio = (int) $request->query('anio', date('Y'));

        $mesesNombres = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        if ($modoVista === 'diario') {
            $periodoSubtitulo = "Día " . Carbon::parse($fecha)->format('d/m/Y');
            $filename = "ASISTENCIA_DIARIA_{$fecha}.pdf";
        } else {
            $nombreMes = $mesesNombres[$mes] ?? $mes;
            $periodoSubtitulo = "Período Mensual {$nombreMes} {$anio}";
            $filename = "ASISTENCIA_MENSUAL_{$anio}_{$mes}.pdf";
        }

        $html = \Illuminate\Support\Facades\View::make('reportes.rrhh.asistencia-pdf', [
            'data'              => $data,
            'totales'           => $totales,
            'periodo_subtitulo' => $periodoSubtitulo,
            'fecha_emision'     => now()->format('d/m/Y H:i'),
        ])->render();

        $pdfContent = \Barryvdh\Snappy\Facades\SnappyPdf::loadHTML($html)
            ->setPaper('letter')
            ->setOrientation('landscape')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0)
            ->setOption('encoding', 'utf-8')
            ->setOption('enable-local-file-access', true)
            ->output();

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
