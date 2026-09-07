<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\ConvenioPago;
use App\Services\Comercial\ConvenioService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ConvenioController extends Controller
{
    public function __construct(
        protected ConvenioService $convenioService
    ) {}

    /**
     * Listado paginado de convenios de pago.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $estado = $request->input('estado');

        $query = ConvenioPago::with(['abonado', 'cuotas'])
            ->orderByDesc('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_convenio', 'like', "%{$search}%")
                    ->orWhereHas('abonado', fn($qa) => $qa->where('codigo', 'like', "%{$search}%")
                        ->orWhere('nombre_completo', 'ilike', "%{$search}%"));
            });
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ], Response::HTTP_OK);
    }

    /**
     * Simulación de cuotas para un convenio de pago.
     */
    public function simular(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'monto_deuda' => 'required|numeric|min:0.01',
            'pago_inicial' => 'required|numeric|min:0',
            'plazo_meses' => 'required|integer|min:1|max:36',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $simulacion = $this->convenioService->simularConvenio(
                (float) $request->input('monto_deuda'),
                (float) $request->input('pago_inicial'),
                (int) $request->input('plazo_meses')
            );

            return response()->json([
                'success' => true,
                'data' => $simulacion,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Suscripción formal de convenio de pagos.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_abonado' => 'required|integer|exists:pgsql.comercial.abonados,id',
            'pago_inicial' => 'required|numeric|min:0',
            'plazo_meses' => 'required|integer|min:1|max:36',
            'glosa' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $convenio = $this->convenioService->suscribirConvenio(
                (int) $request->input('id_abonado'),
                (float) $request->input('pago_inicial'),
                (int) $request->input('plazo_meses'),
                $request->input('glosa'),
                $request->user()?->id ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => "Convenio {$convenio->numero_convenio} suscrito exitosamente en {$convenio->plazo_meses} cuotas.",
                'data' => $convenio,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al suscribir convenio: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
