<?php

declare(strict_types=1);

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Models\Comercial\Abonado;
use App\Models\Comercial\CategoriaTarifaria;
use App\Models\Comercial\LecturaMensual;
use App\Models\Comercial\PeriodoFacturacion;
use App\Models\Comercial\Zona;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReporteComercialController extends Controller
{
    /**
     * Reporte de recaudación en caja por fecha o rango.
     */
    public function recaudacionDiaria(Request $request): JsonResponse
    {
        $fecha = $request->input('fecha', Carbon::today()->toDateString());

        $cobrosAgua = LecturaMensual::with(['abonado', 'facturaSiat', 'cajero'])
            ->whereDate('fecha_pago', $fecha)
            ->where('estado_pago', 'PAGADO')
            ->get();

        $totalRecaudado = (float) $cobrosAgua->sum('total_facturado');
        $totalAgua = (float) $cobrosAgua->sum('monto_agua');
        $totalAlcantarillado = (float) $cobrosAgua->sum('monto_alcantarillado');
        $totalDescuentos = (float) $cobrosAgua->sum('monto_descuento_ley1886');
        $cantidadFacturas = $cobrosAgua->count();

        // Agrupado por cajero
        $porCajero = $cobrosAgua->groupBy('id_cajero')->map(function ($items, $cajeroId) {
            $cajeroNom = $items->first()->cajero?->name ?? "Cajero #{$cajeroId}";
            return [
                'cajero' => $cajeroNom,
                'transacciones' => $items->count(),
                'total' => (float) $items->sum('total_facturado'),
            ];
        })->values();

        return response()->json([
            'success' => true,
            'fecha' => $fecha,
            'metricas' => [
                'total_recaudado' => $totalRecaudado,
                'total_agua' => $totalAgua,
                'total_alcantarillado' => $totalAlcantarillado,
                'total_descuentos_ley1886' => $totalDescuentos,
                'total_transacciones' => $cantidadFacturas,
            ],
            'por_cajero' => $porCajero,
            'detalles' => $cobrosAgua,
        ], Response::HTTP_OK);
    }

    /**
     * Reporte de cartera vencida y morosidad de abonados.
     */
    public function morosidad(Request $request): JsonResponse
    {
        $idZona = $request->input('id_zona');

        $query = Abonado::with(['zona', 'categoria'])
            ->where('meses_mora', '>', 0);

        if (!empty($idZona)) {
            $query->where('id_zona', $idZona);
        }

        $totalAbonadosMora = $query->count();
        $totalDeudaAcumulada = (float) $query->sum('saldo_deuda');

        // Segmentación por antigüedad de mora
        $mora1Mes = (clone $query)->where('meses_mora', 1)->count();
        $mora2Meses = (clone $query)->where('meses_mora', 2)->count();
        $mora3OMas = (clone $query)->where('meses_mora', '>=', 3)->count();

        // Top 10 mayores deudores
        $topDeudores = (clone $query)->orderByDesc('saldo_deuda')->limit(10)->get();

        // Resumen por Zona
        $deudaPorZona = Zona::withCount(['abonados as en_mora' => fn($q) => $q->where('meses_mora', '>', 0)])
            ->get()
            ->map(function ($z) {
                return [
                    'zona' => $z->nombre,
                    'abonados_mora' => $z->en_mora,
                    'total_deuda' => (float) Abonado::where('id_zona', $z->id)->sum('saldo_deuda'),
                ];
            });

        return response()->json([
            'success' => true,
            'metricas' => [
                'total_abonados_mora' => $totalAbonadosMora,
                'total_deuda_acumulada' => $totalDeudaAcumulada,
                'mora_1_mes' => $mora1Mes,
                'mora_2_meses' => $mora2Meses,
                'mora_3_o_mas_meses' => $mora3OMas,
            ],
            'deuda_por_zona' => $deudaPorZona,
            'top_deudores' => $topDeudores,
        ], Response::HTTP_OK);
    }

    /**
     * Reporte de balance de consumo hídrico y facturación mensual.
     */
    public function balanceConsumo(): JsonResponse
    {
        $periodos = PeriodoFacturacion::withCount('lecturas')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->map(function ($p) {
                $lecturas = LecturaMensual::where('id_periodo', $p->id)->get();
                $consumoTotalM3 = (float) $lecturas->sum('consumo_m3');
                $totalFacturado = (float) $lecturas->sum('total_facturado');
                $totalCobrado = (float) $lecturas->where('estado_pago', 'PAGADO')->sum('total_facturado');

                return [
                    'periodo' => $p->periodo,
                    'estado' => $p->estado,
                    'abonados_medidos' => $p->lecturas_count,
                    'volumen_total_m3' => $consumoTotalM3,
                    'monto_facturado_bs' => $totalFacturado,
                    'monto_cobrado_bs' => $totalCobrado,
                    'porcentaje_recaudacion' => $totalFacturado > 0 ? round(($totalCobrado / $totalFacturado) * 100, 1) : 0,
                ];
            });

        return response()->json([
            'success' => true,
            'periodos' => $periodos,
        ], Response::HTTP_OK);
    }
}
