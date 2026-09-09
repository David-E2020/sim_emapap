<?php

declare(strict_types=1);

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Comercial\CajaSesion;
use App\Models\Contabilidad\CentroCosto;
use App\Models\Contabilidad\Comprobante;
use App\Models\Contabilidad\ComprobanteDetalle;
use App\Models\Contabilidad\GestionContable;
use App\Models\Contabilidad\MapeoEnlace;
use App\Models\Contabilidad\PlanCuenta;
use App\Services\Contabilidad\AsientoAutomaticoService;
use App\Services\Contabilidad\ReporteFinancieroPdfService;
use App\Services\Contabilidad\ReportesFinancierosService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ContabilidadController extends Controller
{
    public function __construct(
        private readonly AsientoAutomaticoService $asientoService,
        private readonly ReportesFinancierosService $reportesService,
        private readonly ReporteFinancieroPdfService $pdfService
    ) {}

    // ==========================================
    // 1. PLAN DE CUENTAS
    // ==========================================

    public function planCuentas(Request $request): JsonResponse
    {
        $tipo = $request->query('tipo');
        $nivel = $request->query('nivel');
        $busqueda = $request->query('busqueda');

        $query = PlanCuenta::with('cuentaPadre')
            ->where('_estado', 'ACTIVO')
            ->orderBy('codigo');

        if ($tipo) {
            $query->where('tipo', strtoupper($tipo));
        }

        if ($nivel) {
            $query->where('nivel', (int) $nivel);
        }

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('codigo', 'ilike', "%{$busqueda}%")
                    ->orWhere('nombre', 'ilike', "%{$busqueda}%");
            });
        }

        $cuentas = $query->get();

        return response()->json([
            'success' => true,
            'data' => $cuentas,
        ], Response::HTTP_OK);
    }

    public function cuentasImputables(): JsonResponse
    {
        $cuentas = PlanCuenta::imputables()
            ->orderBy('codigo')
            ->select('id', 'codigo', 'nombre', 'tipo', 'naturaleza', 'permite_movimiento')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cuentas,
        ], Response::HTTP_OK);
    }

    public function guardarCuenta(Request $request): JsonResponse
    {
        $input = $request->all();

        // Normalizar alias
        if (isset($input['naturaleza'])) {
            $nat = strtoupper(trim($input['naturaleza']));
            $input['naturaleza'] = str_starts_with($nat, 'DEUD') ? 'DEUDORA' : 'ACREEDORA';
        }
        if (isset($input['tipo_cuenta']) && !isset($input['tipo'])) {
            $tipoNorm = strtoupper(trim($input['tipo_cuenta']));
            if ($tipoNorm === 'INGRESO') $tipoNorm = 'RECURSO';
            if ($tipoNorm === 'COSTOS') $tipoNorm = 'GASTO';
            $input['tipo'] = $tipoNorm;
        } elseif (isset($input['tipo'])) {
            $tipoNorm = strtoupper(trim($input['tipo']));
            if ($tipoNorm === 'INGRESO') $tipoNorm = 'RECURSO';
            if ($tipoNorm === 'COSTOS') $tipoNorm = 'GASTO';
            $input['tipo'] = $tipoNorm;
        }
        if (isset($input['es_imputable']) && !isset($input['permite_movimiento'])) {
            $input['permite_movimiento'] = (bool) $input['es_imputable'];
        }
        if (isset($input['padre_id']) && !isset($input['id_cuenta_padre'])) {
            $input['id_cuenta_padre'] = $input['padre_id'] ? (int) $input['padre_id'] : null;
        }

        $validator = Validator::make($input, [
            'codigo' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique(PlanCuenta::class, 'codigo')],
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|integer|min:1|max:5',
            'naturaleza' => 'required|in:DEUDORA,ACREEDORA',
            'tipo' => 'required|in:ACTIVO,PASIVO,PATRIMONIO,RECURSO,GASTO,ORDEN',
            'permite_movimiento' => 'required|boolean',
            'id_cuenta_padre' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación de cuenta fallida.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $cuenta = PlanCuenta::create(array_merge($validator->validated(), [
            'estado' => 'ACTIVO',
            '_estado' => 'ACTIVO',
            '_usuario_creacion' => auth()->id() ?? 1,
            '_fecha_creacion' => now(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Cuenta contable creada exitosamente.',
            'data' => $cuenta,
        ], Response::HTTP_OK);
    }

    // ==========================================
    // 2. COMPROBANTES CONTABLES
    // ==========================================

    public function comprobantes(Request $request): JsonResponse
    {
        $tipo = $request->query('tipo');
        $estado = $request->query('estado');
        $origen = $request->query('origen');
        $desde = $request->query('desde') ?? $request->query('fecha_inicio');
        $hasta = $request->query('hasta') ?? $request->query('fecha_fin');
        $busqueda = $request->query('busqueda');

        $query = Comprobante::with(['gestion', 'usuarioElaboracion', 'detalles.cuenta'])
            ->where('_estado', 'ACTIVO')
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($tipo && $tipo !== 'TODOS') {
            $tipoU = strtoupper($tipo);
            if ($tipoU === 'CI' || $tipoU === 'INGRESO') {
                $query->whereIn('tipo', ['CI', 'INGRESO']);
            } elseif ($tipoU === 'CE' || $tipoU === 'EGRESO') {
                $query->whereIn('tipo', ['CE', 'EGRESO']);
            } elseif ($tipoU === 'CD' || $tipoU === 'DIARIO') {
                $query->whereIn('tipo', ['CD', 'DIARIO']);
            } else {
                $query->where('tipo', $tipoU);
            }
        }

        if ($estado && $estado !== 'TODOS') {
            $query->where('estado', strtoupper($estado));
        }

        if ($origen && $origen !== 'TODOS') {
            $query->where('origen_modulo', strtoupper($origen));
        }

        if ($desde) {
            $query->where('fecha', '>=', $desde);
        }

        if ($hasta) {
            $query->where('fecha', '<=', $hasta);
        }

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_comprobante', 'ilike', "%{$busqueda}%")
                    ->orWhere('glosa_principal', 'ilike', "%{$busqueda}%")
                    ->orWhere('beneficiario', 'ilike', "%{$busqueda}%");
            });
        }

        $comprobantes = $query->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $comprobantes->items(),
            'total' => $comprobantes->total(),
            'current_page' => $comprobantes->currentPage(),
            'last_page' => $comprobantes->lastPage(),
        ], Response::HTTP_OK);
    }

    public function showComprobante(int $id): JsonResponse
    {
        $comprobante = Comprobante::with(['gestion', 'usuarioElaboracion', 'usuarioAprobacion', 'detalles.cuenta', 'detalles.centroCosto'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $comprobante,
        ], Response::HTTP_OK);
    }

    public function guardarComprobante(Request $request): JsonResponse
    {
        $input = $request->all();

        // Normalizar tipo (CI -> INGRESO, CE -> EGRESO, CD -> DIARIO)
        if (isset($input['tipo'])) {
            $tipoUpper = strtoupper(trim($input['tipo']));
            if ($tipoUpper === 'CI') $tipoUpper = 'INGRESO';
            if ($tipoUpper === 'CE') $tipoUpper = 'EGRESO';
            if ($tipoUpper === 'CD') $tipoUpper = 'DIARIO';
            $input['tipo'] = $tipoUpper;
        }

        if (isset($input['glosa']) && !isset($input['glosa_principal'])) {
            $input['glosa_principal'] = $input['glosa'];
        }

        if (isset($input['detalles']) && is_array($input['detalles'])) {
            foreach ($input['detalles'] as $idx => $linea) {
                if (isset($linea['plan_cuenta_id']) && !isset($linea['id_cuenta'])) {
                    $input['detalles'][$idx]['id_cuenta'] = $linea['plan_cuenta_id'];
                }
                if (isset($linea['centro_costo_id']) && !isset($linea['id_centro_costo'])) {
                    $input['detalles'][$idx]['id_centro_costo'] = $linea['centro_costo_id'];
                }
                if (isset($linea['glosa']) && !isset($linea['glosa_linea'])) {
                    $input['detalles'][$idx]['glosa_linea'] = $linea['glosa'];
                }
            }
        }

        $validator = Validator::make($input, [
            'tipo' => 'required|in:INGRESO,EGRESO,DIARIO',
            'fecha' => 'required|date',
            'glosa_principal' => 'required|string|min:5',
            'beneficiario' => 'nullable|string|max:255',
            'tipo_documento_respaldo' => 'nullable|string|max:100',
            'numero_documento_respaldo' => 'nullable|string|max:100',
            'detalles' => 'required|array|min:2',
            'detalles.*.id_cuenta' => ['required', \Illuminate\Validation\Rule::exists(PlanCuenta::class, 'id')],
            'detalles.*.id_centro_costo' => ['nullable', \Illuminate\Validation\Rule::exists(CentroCosto::class, 'id')],
            'detalles.*.glosa_linea' => 'nullable|string|max:500',
            'detalles.*.debe' => 'required|numeric|min:0',
            'detalles.*.haber' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validación de comprobante fallida.',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $datos = $validator->validated();
        $fecha = Carbon::parse($datos['fecha']);
        $gestionAnio = (int) $fecha->year;
        $mes = (int) $fecha->month;

        $gestion = GestionContable::firstOrCreate(['gestion' => $gestionAnio], [
            'fecha_inicio' => "{$gestionAnio}-01-01",
            'fecha_fin' => "{$gestionAnio}-12-31",
            'estado' => 'ABIERTA',
        ]);

        $numeroComp = $this->asientoService->generarNumeroCorrelativo($datos['tipo'], $gestionAnio);

        // Validar partida doble previa
        $sumaDebe = 0.0;
        $sumaHaber = 0.0;
        foreach ($datos['detalles'] as $d) {
            $sumaDebe += (float) $d['debe'];
            $sumaHaber += (float) $d['haber'];
        }

        if (round(abs($sumaDebe - $sumaHaber), 2) !== 0.00) {
            return response()->json([
                'success' => false,
                'message' => "El comprobante no cumple con la partida doble obligatoria. Total Debe: Bs {$sumaDebe}, Total Haber: Bs {$sumaHaber} (Diferencia: " . round(abs($sumaDebe - $sumaHaber), 2) . " Bs).",
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $comprobante = DB::transaction(function () use ($datos, $gestion, $mes, $numeroComp, $sumaDebe, $sumaHaber) {
            $comp = Comprobante::create([
                'numero_comprobante' => $numeroComp,
                'tipo' => strtoupper($datos['tipo']),
                'fecha' => $datos['fecha'],
                'id_gestion' => $gestion->id,
                'mes' => $mes,
                'glosa_principal' => $datos['glosa_principal'],
                'beneficiario' => $datos['beneficiario'] ?? null,
                'tipo_documento_respaldo' => $datos['tipo_documento_respaldo'] ?? null,
                'numero_documento_respaldo' => $datos['numero_documento_respaldo'] ?? null,
                'total_debe' => round($sumaDebe, 2),
                'total_haber' => round($sumaHaber, 2),
                'diferencia' => 0.00,
                'estado' => 'APROBADO',
                'origen_modulo' => 'MANUAL',
                'id_usuario_elaboracion' => auth()->id() ?? 1,
                'id_usuario_aprobacion' => auth()->id() ?? 1,
                'fecha_aprobacion' => now(),
            ]);

            $orden = 1;
            foreach ($datos['detalles'] as $linea) {
                ComprobanteDetalle::create([
                    'id_comprobante' => $comp->id,
                    'id_cuenta' => $linea['id_cuenta'],
                    'id_centro_costo' => $linea['id_centro_costo'] ?? null,
                    'glosa_linea' => $linea['glosa_linea'] ?? $comp->glosa_principal,
                    'debe' => round((float) $linea['debe'], 2),
                    'haber' => round((float) $linea['haber'], 2),
                    'orden' => $orden++,
                ]);
            }

            return $comp;
        });

        return response()->json([
            'success' => true,
            'message' => "Comprobante {$comprobante->numero_comprobante} registrado y balanceado exitosamente.",
            'data' => $comprobante,
        ], Response::HTTP_CREATED);
    }

    public function descargarComprobantePdf(int $id): Response
    {
        $comprobante = Comprobante::findOrFail($id);
        $pdf = $this->pdfService->generarComprobantePdf($comprobante);

        return response($pdf, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"Comprobante_{$comprobante->numero_comprobante}.pdf\"",
        ]);
    }

    public function anularComprobante(int $id, Request $request): JsonResponse
    {
        $comprobante = Comprobante::findOrFail($id);

        if ($comprobante->estado === 'ANULADO') {
            return response()->json(['success' => false, 'message' => 'El comprobante ya está anulado.'], Response::HTTP_BAD_REQUEST);
        }

        $comprobante->update([
            'estado' => 'ANULADO',
            '_transaccion' => 'ANULAR',
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Comprobante {$comprobante->numero_comprobante} anulado correctamente.",
            'data' => $comprobante->fresh(),
        ], Response::HTTP_OK);
    }

    // ==========================================
    // 3. CONSOLA DE INTERFASES AUTOMÁTICAS
    // ==========================================

    public function pendientesInterfases(): JsonResponse
    {
        // 1. Cajas cerradas sin comprobante de ingreso
        $sesionesSinContabilizar = CajaSesion::with(['cajero', 'puntoVenta'])
            ->where('estado', 'CERRADA')
            ->whereNotIn('id', function ($query) {
                $query->select('id_referencia_origen')
                    ->from('contabilidad.comprobantes')
                    ->where('origen_modulo', 'COMERCIAL_CAJA')
                    ->where('_estado', 'ACTIVO')
                    ->where('estado', '!=', 'ANULADO');
            })
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        // 2. Planillas mensuales de lecturas sin contabilizar
        $periodosSinContabilizar = DB::table('comercial.periodos_facturacion as p')
            ->where('p.estado', 'CERRADO')
            ->whereNotIn('p.id', function ($query) {
                $query->select('id_referencia_origen')
                    ->from('contabilidad.comprobantes')
                    ->where('origen_modulo', 'COMERCIAL_DEVENGADO')
                    ->where('_estado', 'ACTIVO')
                    ->where('estado', '!=', 'ANULADO');
            })
            ->orderByDesc('p.id')
            ->limit(10)
            ->get();

        // 3. Planillas de sueldos consolidadas de RRHH sin contabilizar
        $planillasRrhhSinContabilizar = DB::table('rrhh.planillas_consolidadas as r')
            ->where('r.estado', 'DECLARADA')
            ->whereNotIn('r.id', function ($query) {
                $query->select('id_referencia_origen')
                    ->from('contabilidad.comprobantes')
                    ->where('origen_modulo', 'RRHH_PLANILLA')
                    ->where('_estado', 'ACTIVO')
                    ->where('estado', '!=', 'ANULADO');
            })
            ->orderByDesc('r.id')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'cajas_pendientes' => $sesionesSinContabilizar,
            'periodos_agua_pendientes' => $periodosSinContabilizar,
            'planillas_rrhh_pendientes' => $planillasRrhhSinContabilizar,
        ], Response::HTTP_OK);
    }

    public function contabilizarCaja(Request $request): JsonResponse
    {
        $sesionId = (int) ($request->input('id_sesion') ?? $request->input('caja_sesion_id'));
        $sesion = CajaSesion::findOrFail($sesionId);

        try {
            $comp = $this->asientoService->generarAsientoArqueoCaja($sesion, auth()->id());

            return response()->json([
                'success' => true,
                'message' => "Sesión de caja {$sesion->numero_sesion} contabilizada con éxito en Comprobante {$comp->numero_comprobante}.",
                'data' => $comp,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al contabilizar sesión de caja: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function contabilizarLecturasAgua(Request $request): JsonResponse
    {
        $periodoId = (int) $request->input('id_periodo');

        if (!$periodoId) {
            $gestion = (int) ($request->input('gestion') ?? date('Y'));
            $mes = (int) ($request->input('mes') ?? date('n'));
            $periodoId = (int) \App\Models\Comercial\PeriodoFacturacion::where('anio', $gestion)
                ->where('mes', $mes)
                ->value('id');

            if (!$periodoId) {
                // Si no existe el período exacto, obtener el período activo o más reciente
                $periodoId = (int) \App\Models\Comercial\PeriodoFacturacion::latest('id')->value('id');
            }
        }

        if (!$periodoId) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un período de facturación para contabilizar.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $comp = $this->asientoService->generarAsientoDevengadoAgua($periodoId, auth()->id());

            return response()->json([
                'success' => true,
                'message' => "Devengamiento de agua potable contabilizado exitosamente en Comprobante {$comp->numero_comprobante}.",
                'data' => $comp,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al contabilizar devengado de lecturas: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function contabilizarPlanillaRrhh(Request $request): JsonResponse
    {
        $planillaId = (int) $request->input('id_planilla');

        if (!$planillaId) {
            $gestion = (int) ($request->input('gestion') ?? date('Y'));
            $mes = (int) ($request->input('mes') ?? date('n'));

            $planillaId = (int) DB::table('rrhh.planillas_consolidadas')
                ->where('gestion', $gestion)
                ->where('mes', $mes)
                ->value('id');

            if (!$planillaId) {
                // Fallback: tomar la última planilla registrada o declarada
                $planillaId = (int) DB::table('rrhh.planillas_consolidadas')->latest('id')->value('id');
            }
        }

        if (!$planillaId) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró una planilla salarial declarada para el período solicitado.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $comp = $this->asientoService->generarAsientoPlanillaSueldos($planillaId, auth()->id());

            return response()->json([
                'success' => true,
                'message' => "Planilla salarial contabilizada exitosamente en Comprobante {$comp->numero_comprobante}.",
                'data' => $comp,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al contabilizar planilla de sueldos: ' . $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // ==========================================
    // 4. LIBROS Y ESTADOS FINANCIEROS
    // ==========================================

    public function libroDiario(Request $request): JsonResponse
    {
        $desde = $request->query('desde') ?? $request->query('fecha_inicio') ?? Carbon::now()->startOfMonth()->toDateString();
        $hasta = $request->query('hasta') ?? $request->query('fecha_fin') ?? Carbon::now()->toDateString();
        $tipo = $request->query('tipo');

        $resultado = $this->reportesService->obtenerLibroDiario($desde, $hasta, $tipo);

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ], Response::HTTP_OK);
    }

    public function libroMayor(Request $request): JsonResponse
    {
        $cuentaId = (int) ($request->query('id_cuenta') ?? $request->query('plan_cuenta_id'));
        $desde = $request->query('desde') ?? $request->query('fecha_inicio') ?? Carbon::now()->startOfYear()->toDateString();
        $hasta = $request->query('hasta') ?? $request->query('fecha_fin') ?? Carbon::now()->toDateString();

        if (!$cuentaId) {
            return response()->json(['success' => false, 'message' => 'Debe especificar una cuenta contable.'], Response::HTTP_BAD_REQUEST);
        }

        $resultado = $this->reportesService->obtenerLibroMayor($cuentaId, $desde, $hasta);

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ], Response::HTTP_OK);
    }

    public function balanceComprobacion(Request $request): JsonResponse
    {
        $gestion = $request->query('gestion');
        $desde = $request->query('desde') ?? $request->query('fecha_inicio') ?? ($gestion ? "{$gestion}-01-01" : Carbon::now()->startOfYear()->toDateString());
        $hasta = $request->query('hasta') ?? $request->query('fecha_fin') ?? ($gestion ? "{$gestion}-12-31" : Carbon::now()->toDateString());

        $resultado = $this->reportesService->obtenerBalanceComprobacion($desde, $hasta);

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ], Response::HTTP_OK);
    }

    public function balanceGeneral(Request $request): JsonResponse
    {
        $gestion = $request->query('gestion');
        $fechaCorte = $request->query('fecha_corte') ?? $request->query('fecha_fin') ?? ($gestion ? "{$gestion}-12-31" : Carbon::now()->toDateString());

        $resultado = $this->reportesService->obtenerBalanceGeneral($fechaCorte);

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ], Response::HTTP_OK);
    }

    public function estadoResultados(Request $request): JsonResponse
    {
        $gestion = $request->query('gestion');
        $desde = $request->query('desde') ?? $request->query('fecha_inicio') ?? ($gestion ? "{$gestion}-01-01" : Carbon::now()->startOfYear()->toDateString());
        $hasta = $request->query('hasta') ?? $request->query('fecha_fin') ?? ($gestion ? "{$gestion}-12-31" : Carbon::now()->toDateString());

        $resultado = $this->reportesService->obtenerEstadoResultados($desde, $hasta);

        return response()->json([
            'success' => true,
            'data' => $resultado,
        ], Response::HTTP_OK);
    }

    // ==========================================
    // 5. PARAMÉTRICAS Y CONFIGURACIÓN
    // ==========================================

    public function centrosCosto(): JsonResponse
    {
        $centros = CentroCosto::where('_estado', 'ACTIVO')->get();

        return response()->json(['success' => true, 'data' => $centros], Response::HTTP_OK);
    }

    public function gestiones(): JsonResponse
    {
        $gestiones = GestionContable::with('periodos')->orderByDesc('gestion')->get();

        return response()->json(['success' => true, 'data' => $gestiones], Response::HTTP_OK);
    }

    public function mapeos(): JsonResponse
    {
        $mapeos = MapeoEnlace::with(['cuentaDefecto', 'centroCostoDefecto'])->get();

        return response()->json(['success' => true, 'data' => $mapeos], Response::HTTP_OK);
    }
}
