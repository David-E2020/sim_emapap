<?php

declare(strict_types=1);

namespace Database\Seeders\Facturacion;

use App\Models\Facturacion\ProductoServicioFactura;
use App\Models\Facturacion\SiatSucursal;
use App\Models\Facturacion\SiatPuntoVenta;
use Illuminate\Database\Seeder;

class EmapapServiciosSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SUCURSAL MATRIZ DE EMAPAP EN PATACAMAYA
        $sucursal = SiatSucursal::updateOrCreate(
            ['codigo_sucursal' => 0],
            [
                'nombre' => 'EMAPAP - OFICINA CENTRAL PATACAMAYA',
                'direccion' => 'Av. Panamericana s/n, Plaza 15 de Agosto',
                'telefono' => '2-8147000',
                'municipio' => 'Patacamaya',
                'departamento' => 'La Paz',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
            ]
        );

        // 2. PUNTO DE VENTA CAJA CENTRAL
        SiatPuntoVenta::updateOrCreate(
            ['id_sucursal' => $sucursal->id, 'codigo_punto_venta' => 0],
            [
                'nombre' => 'Caja Central Recaudaciones Patacamaya',
                'tipo_punto_venta' => 0,
                'descripcion' => 'Punto de venta y cobro de consumo de agua potable y servicios técnicos',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR',
            ]
        );

        // 3. CATÁLOGO DE SERVICIOS Y CONCEPTOS EMAPA PATACAMAYA (Extraído de concepin.DBF y categor.DBF)
        // Homologado con Actividades SIN:
        // 360000: Captación, tratamiento y distribución de agua
        // 370000: Evacuación y tratamiento de aguas residuales
        $serviciosEmapap = [
            // Consumo mensual de agua potable según categorías de Patacamaya
            [
                'codigo_producto_empresa' => 'AGUA-DOM-01',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA DOMICILIARIA (MÍNIMO 6 M³)',
                'precio_unitario' => 12.60,
                'codigo_unidad_medida' => 58, // UNIDAD
            ],
            [
                'codigo_producto_empresa' => 'AGUA-M3-DOM',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO ADICIONAL DE AGUA POTABLE DOMICILIARIA (POR M³)',
                'precio_unitario' => 2.10,
                'codigo_unidad_medida' => 65, // M3
            ],
            [
                'codigo_producto_empresa' => 'AGUA-COM-A',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA COMERCIAL A (MÍNIMO 6 M³)',
                'precio_unitario' => 14.94,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'AGUA-COM-B',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA COMERCIAL B (MÍNIMO 6 M³)',
                'precio_unitario' => 21.12,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'AGUA-PUB-EST',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA ESTATAL O PÚBLICA',
                'precio_unitario' => 21.12,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'AGUA-ESP',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO AGUA POTABLE - CATEGORÍA ESPECIAL',
                'precio_unitario' => 21.48,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'AGUA-LAV-AUT',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'CONSUMO AGUA POTABLE - LAVADO DE AUTOS',
                'precio_unitario' => 21.48,
                'codigo_unidad_medida' => 58,
            ],

            // Servicio de Alcantarillado Sanitario
            [
                'codigo_producto_empresa' => 'ALC-DOM-01',
                'codigo_actividad' => '370000',
                'codigo_producto_sin' => '86312',
                'descripcion' => 'SERVICIO DE ALCANTARILLADO SANITARIO - TARIFA BÁSICA DOMICILIARIA',
                'precio_unitario' => 2.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'ALC-ESP-01',
                'codigo_actividad' => '370000',
                'codigo_producto_sin' => '86312',
                'descripcion' => 'SERVICIO DE ALCANTARILLADO SANITARIO - CATEGORÍA ESPECIAL / LAVADO',
                'precio_unitario' => 10.00,
                'codigo_unidad_medida' => 58,
            ],

            // Servicios Técnicos y Operativos de EMAPAP (De concepin.DBF)
            [
                'codigo_producto_empresa' => 'SRV-01-SUS',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86313',
                'descripcion' => 'FORMULARIO DE SUSPENSIÓN TEMPORAL DE SERVICIO',
                'precio_unitario' => 20.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-02-REC',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86313',
                'descripcion' => 'RECONEXIÓN DE SERVICIO DE AGUA POTABLE',
                'precio_unitario' => 50.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-03-ELE',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86314',
                'descripcion' => 'ELEVACIÓN O TRASLADO DE MEDIDOR DE AGUA',
                'precio_unitario' => 35.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-04-TRF',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86313',
                'descripcion' => 'CAMBIO DE NOMBRE / TRANSFERENCIA DE DERECHO DE AGUA',
                'precio_unitario' => 45.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-05-MUL',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86313',
                'descripcion' => 'MULTA POR RECONEXIÓN CLANDESTINA O MORA',
                'precio_unitario' => 100.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-06-MED',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86314',
                'descripcion' => 'CAMBIO Y REPOSICIÓN DE MEDIDOR DE AGUA POTABLE',
                'precio_unitario' => 180.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-09-IAL',
                'codigo_actividad' => '370000',
                'codigo_producto_sin' => '86312',
                'descripcion' => 'DERECHO DE INSTALACIÓN DE ALCANTARILLADO SANITARIO',
                'precio_unitario' => 450.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-10-IAG',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86313',
                'descripcion' => 'INSTALACIÓN DE AGUA POTABLE (REGULARIZACIÓN / NUEVA)',
                'precio_unitario' => 500.00,
                'codigo_unidad_medida' => 58,
            ],
            [
                'codigo_producto_empresa' => 'SRV-12-CIS',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86311',
                'descripcion' => 'VENTA DE AGUA POTABLE EN CISTERNA / VOLUMEN (M³)',
                'precio_unitario' => 15.00,
                'codigo_unidad_medida' => 65,
            ],
            [
                'codigo_producto_empresa' => 'SRV-13-ACC',
                'codigo_actividad' => '360000',
                'codigo_producto_sin' => '86314',
                'descripcion' => 'VENTA DE ACCESORIOS Y LLAVES DE PASO DE AGUA POTABLE',
                'precio_unitario' => 25.00,
                'codigo_unidad_medida' => 58,
            ],
        ];

        foreach ($serviciosEmapap as $srv) {
            ProductoServicioFactura::updateOrCreate(
                ['codigo_producto_empresa' => $srv['codigo_producto_empresa']],
                array_merge($srv, [
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'CREAR',
                    '_usuario_creacion' => 1,
                ])
            );
        }
    }
}
