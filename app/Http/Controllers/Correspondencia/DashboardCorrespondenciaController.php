<?php

declare(strict_types=1);

namespace App\Http\Controllers\Correspondencia;

use App\Http\Controllers\Controller;
use App\Models\Correspondencia\Derivacion;
use App\Models\Correspondencia\DespachoSalida;
use App\Models\Correspondencia\Documento;
use App\Models\Correspondencia\HojaRuta;
use App\Models\Correspondencia\SolicitudCiudadana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class DashboardCorrespondenciaController extends Controller
{
    public function cards(Request $request): JsonResponse
    {
        $idPersona = auth()->user()?->id_persona ?: (int) $request->input('id_persona', 1);

        // Métricas de Funcionario
        $pendientesFirma = Documento::where('estado', 'PENDIENTE_FIRMA')->count();
        $pendientesBandeja = Derivacion::where('id_funcionario_destino', $idPersona)
            ->whereIn('estado_derivacion', ['PENDIENTE_RECEPCION', 'RECIBIDO'])
            ->count();
        $atendidasDerivadas = Derivacion::where('id_funcionario_origen', $idPersona)->count();

        // Métricas Globales
        $totalHojasRuta = HojaRuta::where('_estado', 'ACTIVO')->count();
        $hojasRutaConcluidas = HojaRuta::where('estado', 'CONCLUIDO')->count();
        $solicitudesCiudadanas = SolicitudCiudadana::where('estado_solicitud', 'REGISTRADA')->count();
        $despachosPendientes = DespachoSalida::where('estado_despacho', 'PENDIENTE_DESPACHO')->count();
        $totalDocumentosFirmados = Documento::where('estado', 'FIRMADO')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'funcionario' => [
                    ['titulo' => 'Correspondencia Pendiente', 'contador' => $pendientesBandeja, 'color' => 'primary', 'icon' => 'mdi-inbox-arrow-down'],
                    ['titulo' => 'Documentos por Firmar', 'contador' => $pendientesFirma, 'color' => 'indigo', 'icon' => 'mdi-draw-pen'],
                    ['titulo' => 'Trámites Atendidos / Derivados', 'contador' => $atendidasDerivadas, 'color' => 'success', 'icon' => 'mdi-send-check'],
                    ['titulo' => 'Solicitudes Web Pendientes', 'contador' => $solicitudesCiudadanas, 'color' => 'amber darken-3', 'icon' => 'mdi-account-voice'],
                ],
                'global' => [
                    'total_hojas_ruta' => $totalHojasRuta,
                    'concluidas' => $hojasRutaConcluidas,
                    'porcentaje_concluidas' => $totalHojasRuta > 0 ? round(($hojasRutaConcluidas / $totalHojasRuta) * 100) : 0,
                    'documentos_firmados' => $totalDocumentosFirmados,
                    'despachos_pendientes' => $despachosPendientes,
                ],
            ],
        ], Response::HTTP_OK);
    }

    public function charts(Request $request): JsonResponse
    {
        $gestion = (int) $request->input('gestion', date('Y'));

        // Agrupación de Hojas de Ruta por origen
        $porOrigen = HojaRuta::select(
            'origen',
            DB::raw('COUNT(id) as total')
        )
            ->where('gestion', $gestion)
            ->groupBy('origen')
            ->get();

        // Agrupación de Hojas de Ruta por prioridad
        $porPrioridad = HojaRuta::select(
            'prioridad',
            DB::raw('COUNT(id) as total')
        )
            ->where('gestion', $gestion)
            ->groupBy('prioridad')
            ->get();

        // Agrupación por Estado
        $porEstado = HojaRuta::select(
            'estado',
            DB::raw('COUNT(id) as total')
        )
            ->where('gestion', $gestion)
            ->groupBy('estado')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'por_origen' => $porOrigen,
                'por_prioridad' => $porPrioridad,
                'por_estado' => $porEstado,
            ],
        ], Response::HTTP_OK);
    }
}
