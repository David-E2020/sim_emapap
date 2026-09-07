<?php

declare(strict_types=1);

namespace App\Services\Comercial;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CategoriaTarifaria;

class TarifarioAguaService
{
    /**
     * Calcula la liquidación de consumo para un abonado y volumen en m³.
     *
     * @return array{
     *   consumo_m3: float,
     *   monto_agua: float,
     *   monto_alcantarillado: float,
     *   monto_descuento_ley1886: float,
     *   monto_otros: float,
     *   total_facturado: float,
     *   detalles: array
     * }
     */
    public function calcularLiquidacion(
        Abonado $abonado,
        float $lecturaAnterior,
        float $lecturaActual,
        float $otrosCargos = 0.00
    ): array {
        $consumo = max(0.00, round($lecturaActual - $lecturaAnterior, 2));
        $categoria = $abonado->categoria;

        if (!$categoria) {
            $categoria = CategoriaTarifaria::where('codigo', 'D')->firstOrFail();
        }

        $volumenBase = (float) $categoria->volumen_base;
        $tarifaMinima = (float) $categoria->tarifa_minima;
        $tarifaExcedenteBase = (float) $categoria->tarifa_excedente_base;
        $tarifaAlcantarillado = (float) $categoria->tarifa_alcantarillado;

        // 1. Cálculo del Importe de Agua Potable
        $montoAgua = 0.00;
        $detalles = [];

        if ($consumo <= $volumenBase) {
            $montoAgua = $tarifaMinima;
            $detalles['consumo_base_m3'] = $consumo;
            $detalles['excedente_m3'] = 0.00;
            $detalles['tarifa_aplicada'] = $tarifaMinima;
        } else {
            $excedente = round($consumo - $volumenBase, 2);
            $detalles['consumo_base_m3'] = $volumenBase;
            $detalles['excedente_m3'] = $excedente;

            // Verificar si la categoría tiene tarifas escalonadas registradas
            $escalones = $categoria->tarifasEscalonadas;
            if ($escalones->isNotEmpty()) {
                $costoExcedente = 0.00;
                $metrosRestantes = $excedente;

                foreach ($escalones as $escalon) {
                    if ($metrosRestantes <= 0) {
                        break;
                    }
                    $rango = (float) ($escalon->hasta_m3 ? ($escalon->hasta_m3 - $escalon->desde_m3 + 1) : $metrosRestantes);
                    $m3EnRango = min($metrosRestantes, $rango);
                    $costoExcedente += $m3EnRango * (float) $escalon->precio_m3;
                    $metrosRestantes -= $m3EnRango;
                }
                $montoAgua = round($tarifaMinima + $costoExcedente, 2);
            } else {
                // Cálculo lineal con tarifa_excedente_base
                $costoExcedente = round($excedente * $tarifaExcedenteBase, 2);
                $montoAgua = round($tarifaMinima + $costoExcedente, 2);
            }
        }

        // 2. Cálculo de Alcantarillado
        $montoAlcantarillado = 0.00;
        if ($abonado->tiene_alcantarillado) {
            $montoAlcantarillado = $tarifaAlcantarillado;
        }

        $subtotal = round($montoAgua + $montoAlcantarillado, 2);

        // 3. Descuento Tercera Edad (Ley 1886)
        // Aplica 20% sobre el servicio básico para categoría Domiciliaria
        $montoDescuentoLey1886 = 0.00;
        if ($categoria->aplica_ley_1886 && $abonado->es_tercera_edad) {
            $montoDescuentoLey1886 = round($subtotal * 0.20, 2);
        }

        // 4. Total Facturado
        $totalFacturado = max(0.00, round($subtotal - $montoDescuentoLey1886 + $otrosCargos, 2));

        return [
            'consumo_m3' => $consumo,
            'monto_agua' => $montoAgua,
            'monto_alcantarillado' => $montoAlcantarillado,
            'monto_descuento_ley1886' => $montoDescuentoLey1886,
            'monto_otros' => round($otrosCargos, 2),
            'total_facturado' => $totalFacturado,
            'detalles' => $detalles,
        ];
    }
}
