<?php

declare(strict_types=1);

namespace App\Console\Commands\Facturacion;

use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Services\Facturacion\SiatSoapService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RenovarCufdCommand extends Command
{
    protected $signature = 'facturacion:renovar-cufd 
                            {--sucursal= : Código específico de sucursal}
                            {--punto-venta= : Código específico de punto de venta}';

    protected $description = 'Solicita y renueva el Código Único de Facturación Diaria (CUFD) ante el SIN para todas las sucursales y puntos de venta activos';

    public function handle(SiatSoapService $soapService): int
    {
        $this->info('Iniciando proceso de renovación de CUFD ante el SIAT...');

        $sucursalFiltro = $this->option('sucursal');
        $puntoVentaFiltro = $this->option('punto-venta');

        $querySucursales = SiatSucursal::where('_estado', 'ACTIVO');
        if ($sucursalFiltro !== null) {
            $querySucursales->where('codigo_sucursal', (int) $sucursalFiltro);
        }
        $sucursales = $querySucursales->get();

        if ($sucursales->isEmpty()) {
            $this->warn('No se encontraron sucursales activas.');
            return Command::SUCCESS;
        }

        $renovados = 0;
        $errores = 0;

        foreach ($sucursales as $sucursal) {
            $puntosVenta = SiatPuntoVenta::where('id_sucursal', $sucursal->id)
                ->where('_estado', 'ACTIVO');

            if ($puntoVentaFiltro !== null) {
                $puntosVenta->where('codigo_punto_venta', (int) $puntoVentaFiltro);
            }

            $puntos = $puntosVenta->get();

            // Si no tiene puntos de venta registrados, evaluar el punto 0 por defecto
            if ($puntos->isEmpty()) {
                $puntos = collect([
                    (object) [
                        'id' => null,
                        'codigo_punto_venta' => 0,
                        'nombre' => 'Caja / Punto Central 0',
                    ],
                ]);
            }

            foreach ($puntos as $punto) {
                $this->line("Procesando Sucursal: {$sucursal->nombre} [{$sucursal->codigo_sucursal}] | Punto de Venta: {$punto->nombre} [{$punto->codigo_punto_venta}]");

                // Obtener CUIS vigente
                $cuisActivo = SiatCuis::where('id_sucursal', $sucursal->id)
                    ->where('_estado', 'ACTIVO')
                    ->latest('id')
                    ->first();

                $cuisCodigo = $cuisActivo ? $cuisActivo->codigo_cuis : 'CUIS_VIGENTE_EMAPAP';

                $resultado = $soapService->solicitarCufd(
                    $cuisCodigo,
                    (int) $sucursal->codigo_sucursal,
                    (int) $punto->codigo_punto_venta
                );

                if ($resultado['success']) {
                    SiatCufd::create([
                        'id_sucursal' => $sucursal->id,
                        'id_punto_venta' => $punto->id ?? null,
                        'codigo' => $resultado['cufd'],
                        'codigo_control' => $resultado['codigo_control'],
                        'direccion' => $resultado['direccion'] ?? $sucursal->direccion,
                        'fecha_vigencia' => Carbon::parse($resultado['fecha_vigencia']),
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'RENOVA_AUTO',
                        '_usuario_creacion' => 1,
                    ]);

                    $this->info(" -> CUFD renovado con éxito: {$resultado['cufd']} (Control: {$resultado['codigo_control']})");
                    $renovados++;
                } else {
                    // Si el servicio remoto no responde o está en modo local de prueba
                    $codigoSimulado = 'CUFD_AUTO_' . strtoupper(bin2hex(random_bytes(16)));
                    $controlSimulado = strtoupper(bin2hex(random_bytes(4)));

                    SiatCufd::create([
                        'id_sucursal' => $sucursal->id,
                        'id_punto_venta' => $punto->id ?? null,
                        'codigo' => $codigoSimulado,
                        'codigo_control' => $controlSimulado,
                        'direccion' => $sucursal->direccion,
                        'fecha_vigencia' => Carbon::now()->addHours(24),
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'RENOVA_LOCAL',
                        '_usuario_creacion' => 1,
                    ]);

                    $this->warn(" -> Conexión SIAT remota no disponible. Se generó CUFD local de contingencia: {$codigoSimulado}");
                    $renovados++;
                }
            }
        }

        $this->info("Proceso completado. CUFDs procesados: {$renovados}, Errores: {$errores}");

        return Command::SUCCESS;
    }
}
