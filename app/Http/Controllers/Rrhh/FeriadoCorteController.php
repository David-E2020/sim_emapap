<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rrhh;

use App\Http\Controllers\Controller;
use App\Models\Rrhh\Departamento;
use App\Models\Rrhh\Feriado;
use App\Services\Audit\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class FeriadoCorteController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function listarFeriados(Request $request): JsonResponse
    {
        $anio = (int)$request->query('anio', date('Y'));
        $feriados = Feriado::with('departamento')
            ->where('anio', $anio)
            ->where('_estado', 'ACTIVO')
            ->orderBy('mes')
            ->orderBy('dia')
            ->get();

        $departamentos = Departamento::orderBy('id')->get();

        return response()->json([
            'success' => true,
            'anio' => $anio,
            'feriados' => $feriados,
            'departamentos' => $departamentos,
        ], Response::HTTP_OK);
    }

    public function storeFeriado(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:150',
            'dia' => 'required|integer|min:1|max:31',
            'mes' => 'required|integer|min:1|max:12',
            'anio' => 'required|integer',
            'es_feriado_nacional' => 'nullable|boolean',
            'id_departamento' => 'nullable|integer|exists:rrhh.departamentos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $feriado = Feriado::create([
                'nombre' => strtoupper(trim((string)$request->input('nombre'))),
                'dia' => (int)$request->input('dia'),
                'mes' => (int)$request->input('mes'),
                'dia_feriado' => (int)$request->input('dia'),
                'anio' => (int)$request->input('anio'),
                'es_feriado_nacional' => (bool)$request->input('es_feriado_nacional', true),
                'id_departamento' => $request->input('es_feriado_nacional') ? null : $request->input('id_departamento'),
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Feriado registrado exitosamente.',
                'data' => $feriado,
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al registrar feriado', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al registrar feriado.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function listarFechasCorte(): JsonResponse
    {
        $cortes = DB::table('rrhh.fechas_cortes')
            ->where('_estado', 'ACTIVO')
            ->orderBy('gestion', 'desc')
            ->orderBy('mes', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cortes,
        ], Response::HTTP_OK);
    }

    public function storeFechaCorte(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mes' => 'required|integer|min:1|max:12',
            'gestion' => 'nullable|integer',
            'anio' => 'nullable|integer',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $gestion = (int)($request->input('gestion') ?: $request->input('anio', date('Y')));
            $inicio = \Carbon\Carbon::parse($request->input('fecha_inicio'));
            $fin = \Carbon\Carbon::parse($request->input('fecha_fin'));

            DB::table('rrhh.fechas_cortes')->insert([
                'mes' => (int)$request->input('mes'),
                'gestion' => $gestion,
                'dia_inicio' => (int)$inicio->day,
                'dia_fin' => (int)$fin->day,
                'fecha_inicio' => $request->input('fecha_inicio'),
                'fecha_fin' => $request->input('fecha_fin'),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
                '_usuario_creacion' => auth()->id() ?? 1,
                '_fecha_creacion' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Fecha de corte mensual configurada correctamente.',
            ], Response::HTTP_CREATED);
        } catch (\Throwable $ex) {
            Log::error('Error al configurar fecha corte', ['exception' => $ex->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error al registrar fecha de corte.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
