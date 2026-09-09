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
        $this->admin = User::first();
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
                ],
                'recaudacion' => [
                    'total_recaudado',
                    'cajas_abiertas',
                ],
                'periodo_actual' => [
                    'nombre',
                    'mes',
                    'gestion',
                    'total_facturado',
                    'total_m3',
                    'total_lecturas',
                ],
                'sin' => [
                    'total_emitidas',
                    'validas',
                    'contingencia',
                    'anuladas',
                    'credito_fiscal',
                    'eventos_contingencia',
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
}
