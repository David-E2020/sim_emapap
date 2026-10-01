<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected $token;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('email', 'admin@emapa.gob.bo')->first() ?: User::first();
        if ($this->admin) {
            $this->token = JWTAuth::fromUser($this->admin);
        }
    }

    /**
     * El endpoint /api/dashboard/metricas debe rechazar peticiones no autenticadas.
     */
    public function test_dashboard_metricas_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/dashboard/metricas');
        $response->assertStatus(401);
    }

    /**
     * El endpoint /api/dashboard/metricas retorna la estructura ejecutiva completa.
     */
    public function test_dashboard_metricas_retorna_datos_ejecutivos(): void
    {
        $this->assertNotNull($this->token, 'Token JWT debe existir para el usuario admin');

        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/dashboard/metricas');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'catastro' => [
                    'total_abonados',
                    'activos',
                    'cortados',
                    'con_medidor',
                    'con_alcantarillado',
                    'nuevos' => [
                        'hoy',
                        'semana',
                        'mes',
                        'anio',
                        'total',
                    ],
                ],
                'recaudacion' => [
                    'total_recaudado',
                    'cajas_abiertas',
                    'desglose' => [
                        'hoy' => ['monto', 'cantidad', 'label'],
                        'semana' => ['monto', 'cantidad', 'label'],
                        'mes' => ['monto', 'cantidad', 'label'],
                        'anio' => ['monto', 'cantidad', 'label'],
                        'total' => ['monto', 'cantidad', 'label'],
                    ],
                ],
                'periodo_actual' => [
                    'id',
                    'nombre',
                    'mes',
                    'gestion',
                    'total_facturado',
                    'total_m3',
                    'total_lecturas',
                ],
                'periodos_disponibles',
                'gestiones_disponibles',
                'sin' => [
                    'total_emitidas',
                    'validas',
                    'contingencia',
                    'anuladas',
                    'credito_fiscal',
                    'eventos_contingencia',
                    'desglose' => [
                        'hoy' => ['cantidad', 'monto', 'label'],
                        'semana' => ['cantidad', 'monto', 'label'],
                        'mes' => ['cantidad', 'monto', 'label'],
                        'anio' => ['cantidad', 'monto', 'label'],
                        'total' => ['cantidad', 'monto', 'label'],
                    ],
                ],
                'mora' => [
                    'socios_en_mora',
                    'deuda_total',
                ],
                'modulos' => [
                    'contabilidad_comprobantes',
                    'contabilidad_cuentas',
                    'rrhh_personal',
                    'correspondencia_hojas_ruta',
                ],
                'graficos' => [
                    'categorias',
                    'facturado',
                    'consumo_m3',
                    'donut_labels',
                    'donut_series',
                ],
                'ultimos_pagos',
            ]);
    }

    /**
     * El endpoint /api/dashboard/metricas permite filtrar por un periodo especifico.
     */
    public function test_dashboard_metricas_permite_filtrar_por_periodo(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/dashboard/metricas?id_periodo=210');

        $response->assertStatus(200);
        $this->assertEquals(210, $response->json('periodo_actual.id'));
        $this->assertEquals('06/2026', $response->json('periodo_actual.nombre'));
    }

    /**
     * El endpoint /api/dashboard/metricas permite filtrar por rango de fechas personalizado.
     */
    public function test_dashboard_metricas_permite_filtrar_por_rango_fechas(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->token)
            ->getJson('/api/dashboard/metricas?fecha_desde=2026-09-01&fecha_hasta=2026-09-30');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('filtro_personalizado'));
        $this->assertArrayHasKey('personalizado', $response->json('recaudacion.desglose'));
        $this->assertGreaterThan(0, $response->json('recaudacion.desglose.personalizado.monto'));
        $this->assertEquals(
            round((float)$response->json('recaudacion.desglose.personalizado.monto_agua') + (float)$response->json('recaudacion.desglose.personalizado.monto_ventanilla'), 2),
            round((float)$response->json('recaudacion.desglose.personalizado.monto'), 2)
        );
    }
}
