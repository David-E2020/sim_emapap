<?php

declare(strict_types=1);

namespace Tests\Feature\Facturacion;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class CuadraturaFiscalRcvYExportacionExcelTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::find(1) ?: User::where('email', 'admin@emapa.gob.bo')->first();
        if (!$this->admin) {
            $this->admin = User::first() ?? User::factory()->create();
        }

        $rolAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Administrador General', 'guard_name' => 'api']);
        if (!$this->admin->hasRole('Administrador General')) {
            $this->admin->assignRole($rolAdmin);
        }

        $this->token = JWTAuth::fromUser($this->admin);
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test 1: Verificar cuadratura matemática oficial del RCV para Junio 2026.
     * Cuadra 100% con la Constancia Oficial Formulario 200 SIN (Hoja Impresa).
     */
    public function test_cuadratura_matematica_junio_2026_coincide_con_constancia_sin(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/reportes/libro-ventas?fecha_desde=2026-06-01&fecha_hasta=2026-06-30&limite=100');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $resumen = $response->json('resumen');
        $this->assertEquals(5380, $resumen['total_registros'], 'Total facturas Junio debe ser 5.380');
        $this->assertEquals(5373, $resumen['cantidad_validas'], 'Facturas válidas Junio debe ser 5.373');
        $this->assertEquals(7, $resumen['cantidad_anuladas'], 'Facturas anuladas Junio debe ser 7');
        $this->assertEquals(106068.14, $resumen['total_facturado'], 'Total Facturado debe ser Bs 106.068,14');
        $this->assertEquals(5586.00, $resumen['total_tasas'], 'Total Tasas No IVA debe ser Bs 5.586,00');
        $this->assertEquals(497.70, $resumen['total_descuento'], 'Total Descuentos Ley 1886 debe ser Bs 497,70');
        $this->assertEquals(99984.44, $resumen['total_base_debito_fiscal'], 'Base Débito Fiscal debe ser Bs 99.984,44');
        $this->assertEquals(12997.98, $resumen['debito_fiscal_iva'], 'Débito Fiscal IVA 13% Form. 200 debe ser Bs 12.997,98');

        // Cédula oficial RCV Formulario 200
        $cedula = $response->json('consolidacion_rcv.ventas');
        $this->assertEquals(5380, $cedula['cantidad_facturas']);
        $this->assertEquals(5373, $cedula['facturas_validas']);
        $this->assertEquals(7, $cedula['facturas_anuladas']);
        $this->assertEquals(106068.14, $cedula['importe_total']);
        $this->assertEquals(5586.00, $cedula['importe_tasas']);
        $this->assertEquals(497.70, $cedula['importe_descuentos']);
        $this->assertEquals(99984.44, $cedula['importe_con_derecho_df']);
        $this->assertEquals(12997.98, $cedula['importe_iva_df']);
    }

    /**
     * Test 2: Verificar cuadratura matemática oficial del RCV para Julio 2026.
     */
    public function test_cuadratura_matematica_julio_2026_coincide_con_constancia_sin(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/reportes/libro-ventas?fecha_desde=2026-07-01&fecha_hasta=2026-07-31&limite=100');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $resumen = $response->json('resumen');
        $this->assertEquals(5352, $resumen['total_registros'], 'Total facturas Julio debe ser 5.352');
        $this->assertEquals(5321, $resumen['cantidad_validas'], 'Facturas válidas Julio debe ser 5.321');
        $this->assertEquals(31, $resumen['cantidad_anuladas'], 'Facturas anuladas Julio debe ser 31');
        $this->assertEquals(112550.44, $resumen['total_facturado'], 'Total Facturado Julio debe ser Bs 112.550,44');
        $this->assertEquals(105611.56, $resumen['total_base_debito_fiscal'], 'Base Débito Fiscal Julio debe ser Bs 105.611,56');
        $this->assertEquals(13729.50, $resumen['debito_fiscal_iva'], 'Débito Fiscal IVA 13% Julio debe ser Bs 13.729,50');
    }

    /**
     * Test 3: Verificar que la Bandeja de Facturas (/api/facturacion/facturas)
     * retorna los KPI agregados idénticos al Formulario 200 del SIN.
     */
    public function test_bandeja_facturas_retorna_metricas_oficiales_y_orden_correcto(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/facturacion/facturas?fecha_inicio=2026-06-01&fecha_fin=2026-06-30&per_page=15');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'resumen' => [
                    'total_registros',
                    'cantidad_validas',
                    'cantidad_anuladas',
                    'total_facturado',
                    'total_tasas',
                    'total_base_debito_fiscal',
                    'total_debito_fiscal',
                ],
            ]);

        $resumen = $response->json('resumen');
        $this->assertEquals(5380, $resumen['total_registros']);
        $this->assertEquals(5373, $resumen['cantidad_validas']);
        $this->assertEquals(7, $resumen['cantidad_anuladas']);
        $this->assertEquals(106068.14, $resumen['total_facturado']);
        $this->assertEquals(5586.00, $resumen['total_tasas']);
        $this->assertEquals(99984.44, $resumen['total_base_debito_fiscal']);
        $this->assertEquals(12997.98, $resumen['total_debito_fiscal']);

        // Verificar que la primera factura devuelta es 29887 (orden descendente secuencial)
        $primeraFactura = $response->json('data.0');
        $this->assertEquals(29887, (int) $primeraFactura['numero_factura'], 'La primera factura debe ser la 29887');
    }

    /**
     * Test 4: Exportación XLSX de la Bandeja de Facturas:
     * Valida encabezado con NIT oficial (1002393029), ordenación numérica descendente,
     * códigos de abonado presentes y fórmulas de sumatoria cuadran al centavo.
     */
    public function test_exportar_excel_bandeja_genera_documento_valido_y_cuadrado(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->get('/api/facturacion/facturas/exportar-excel?formato=xlsx&fecha_inicio=2026-06-01&fecha_fin=2026-06-30');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->contains('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'));

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_bandeja_') . '.xlsx';
        file_put_contents($tmpFile, $response->streamedContent());

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tmpFile);
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Título institucional con NIT oficial 1002393029
        $tituloA1 = (string) $sheet->getCell('A1')->getValue();
        $this->assertStringContainsString('1002393029', $tituloA1, 'El encabezado debe contener el NIT 1002393029');
        $this->assertStringContainsString('EMAPA', $tituloA1);

        // 2. Tarjetas de resumen en filas 5 y 6
        $this->assertEquals('5380', (string) $sheet->getCell('B6')->getValue());
        $this->assertEquals('5373', (string) $sheet->getCell('D6')->getValue());
        $this->assertEquals('7', (string) $sheet->getCell('H6')->getValue());
        $this->assertEquals('Bs 106,068.14', (string) $sheet->getCell('J6')->getValue());
        $this->assertEquals('Bs 5,586.00', (string) $sheet->getCell('L6')->getValue());
        $this->assertEquals('Bs 99,984.44', (string) $sheet->getCell('N6')->getValue());
        $this->assertEquals('Bs 12,997.98', (string) $sheet->getCell('P6')->getValue());

        // 3. Primera fila de datos debe ser Factura 29887 (orden descendente secuencial)
        $this->assertEquals('29887', (string) $sheet->getCell('B9')->getValue());
        $this->assertEquals('02071', (string) $sheet->getCell('D9')->getValue(), 'Código de abonado no debe estar vacío');

        // 4. Última fila contiene la sumatoria exacta
        $lastRow = $sheet->getHighestRow();
        $this->assertEquals(106068.14, round((float) $sheet->getCell("K{$lastRow}")->getCalculatedValue(), 2));
        $this->assertEquals(5586.00, round((float) $sheet->getCell("L{$lastRow}")->getCalculatedValue(), 2));
        $this->assertEquals(99984.44, round((float) $sheet->getCell("M{$lastRow}")->getCalculatedValue(), 2));

        unlink($tmpFile);
    }

    /**
     * Test 5: Exportación XLSX Oficial RCV (Libro de Ventas SIAT):
     * Valida existencia de las 2 pestañas oficiales (Constancia RCV + Libro de Ventas).
     */
    public function test_exportar_excel_rcv_libro_ventas_oficial_genera_dos_hojas(): void
    {
        $response = $this->withHeaders($this->authHeaders())
            ->get('/api/facturacion/reportes/libro-ventas/excel?fecha_desde=2026-06-01&fecha_hasta=2026-06-30');

        $response->assertStatus(200);

        $tmpFile = tempnam(sys_get_temp_dir(), 'test_rcv_') . '.xlsx';
        file_put_contents($tmpFile, $response->streamedContent());

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tmpFile);

        // Debe contener Hoja 1: Constancia RCV y Hoja 2: Libro de Ventas
        $sheetNames = $spreadsheet->getSheetNames();
        $this->assertContains('Constancia RCV', $sheetNames);
        $this->assertContains('Libro de Ventas', $sheetNames);

        $sheetConstancia = $spreadsheet->getSheetByName('Constancia RCV');
        $this->assertNotNull($sheetConstancia);

        unlink($tmpFile);
    }
}
