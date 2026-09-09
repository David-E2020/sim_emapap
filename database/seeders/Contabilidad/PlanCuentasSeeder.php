<?php

declare(strict_types=1);

namespace Database\Seeders\Contabilidad;

use App\Models\Contabilidad\CentroCosto;
use App\Models\Contabilidad\GestionContable;
use App\Models\Contabilidad\MapeoEnlace;
use App\Models\Contabilidad\PeriodoContable;
use App\Models\Contabilidad\PlanCuenta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanCuentasSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Gestión Fiscal 2026 y sus 12 Periodos Mensuales
        $gestion = GestionContable::firstOrCreate(
            ['gestion' => 2026],
            [
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'ABIERTA',
                'observaciones' => 'Gestión Fiscal Anual 2026 - EMAPAP Patacamaya',
            ]
        );

        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        foreach ($meses as $num => $nom) {
            PeriodoContable::firstOrCreate(
                ['id_gestion' => $gestion->id, 'mes' => $num],
                ['nombre' => $nom, 'estado' => 'ABIERTO']
            );
        }

        // 2. Crear Centros de Costo Institucionales
        $centros = [
            ['codigo' => 'CC-01', 'nombre' => 'Administración General y Finanzas', 'descripcion' => 'Gerencia, contabilidad y dirección general'],
            ['codigo' => 'CC-02', 'nombre' => 'Comercial, Facturación y Cobranzas', 'descripcion' => 'Ventanillas, padrón, lectura y atención al usuario'],
            ['codigo' => 'CC-03', 'nombre' => 'Operaciones Técnicas y Redes', 'descripcion' => 'Mantenimiento de tuberías, cortes y conexiones'],
            ['codigo' => 'CC-04', 'nombre' => 'Planta de Tratamiento y Pozos', 'descripcion' => 'Producción de agua potable y control de calidad'],
        ];

        foreach ($centros as $c) {
            CentroCosto::updateOrCreate(['codigo' => $c['codigo']], $c);
        }

        $ccComercial = CentroCosto::where('codigo', 'CC-02')->first();
        $ccAdmin = CentroCosto::where('codigo', 'CC-01')->first();

        // 3. Catálogo Jerárquico del Plan de Cuentas (Normas Básicas Contabilidad Integrada Bolivia)
        $cuentas = [
            // NIVEL 1: GRUPOS
            ['codigo' => '1', 'nombre' => 'ACTIVO', 'nivel' => 1, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => null],
            ['codigo' => '2', 'nombre' => 'PASIVO', 'nivel' => 1, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => null],
            ['codigo' => '3', 'nombre' => 'PATRIMONIO', 'nivel' => 1, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PATRIMONIO', 'permite' => false, 'padre' => null],
            ['codigo' => '5', 'nombre' => 'RECURSOS Y VENTAS', 'nivel' => 1, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => false, 'padre' => null],
            ['codigo' => '6', 'nombre' => 'GASTOS Y COSTOS OPERATIVOS', 'nivel' => 1, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => false, 'padre' => null],

            // 1. ACTIVO
            ['codigo' => '1.1', 'nombre' => 'ACTIVO CORRIENTE', 'nivel' => 2, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1'],
            ['codigo' => '1.1.1', 'nombre' => 'DISPONIBLE', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1.1'],
            ['codigo' => '1.1.1.01', 'nombre' => 'Caja Recaudadora', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1.1.1'],
            ['codigo' => '1.1.1.01.001', 'nombre' => 'Caja Central Recaudación Ventanilla', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.1.01'],
            ['codigo' => '1.1.1.01.002', 'nombre' => 'Caja Chica y Fondo Fijo Rotativo', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.1.01'],

            ['codigo' => '1.1.1.02', 'nombre' => 'Bancos Fiscales y Cuentas Corrientes', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1.1.1'],
            ['codigo' => '1.1.1.02.001', 'nombre' => 'Banco Unión Cuenta Única Fiscal EMAPAP', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.1.02'],
            ['codigo' => '1.1.1.02.002', 'nombre' => 'Banco Unión Recaudación QR y Transferencias', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.1.02'],

            ['codigo' => '1.1.2', 'nombre' => 'EXIGIBLE A CORTO PLAZO', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1.1'],
            ['codigo' => '1.1.2.01', 'nombre' => 'Cuentas por Cobrar Servicios de Agua Potable', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1.1.2'],
            ['codigo' => '1.1.2.01.001', 'nombre' => 'Abonados Domiciliarios por Cobrar', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.2.01'],
            ['codigo' => '1.1.2.01.002', 'nombre' => 'Abonados Comerciales e Institucionales por Cobrar', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.2.01'],
            ['codigo' => '1.1.2.01.003', 'nombre' => 'Convenios de Pago Refinanciados por Cobrar', 'nivel' => 5, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.1.2.01'],

            ['codigo' => '1.2', 'nombre' => 'ACTIVO NO CORRIENTE (BIENES DE USO)', 'nivel' => 2, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => false, 'padre' => '1'],
            ['codigo' => '1.2.1', 'nombre' => 'Redes de Agua Potable y Alcantarillado', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.2'],
            ['codigo' => '1.2.2', 'nombre' => 'Maquinaria, Bombas y Equipos de Tratamiento', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.2'],
            ['codigo' => '1.2.3', 'nombre' => 'Equipos de Computación y Comunicación', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'ACTIVO', 'permite' => true, 'padre' => '1.2'],

            // 2. PASIVO
            ['codigo' => '2.1', 'nombre' => 'PASIVO CORRIENTE', 'nivel' => 2, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => '2'],
            ['codigo' => '2.1.1', 'nombre' => 'SERVICIOS PERSONALES POR PAGAR', 'nivel' => 3, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => '2.1'],
            ['codigo' => '2.1.1.01', 'nombre' => 'Sueldos y Salarios por Pagar', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.1'],
            ['codigo' => '2.1.1.02', 'nombre' => 'Refrigerios del Personal por Pagar', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.1'],
            ['codigo' => '2.1.1.03', 'nombre' => 'Aguinaldos de Navidad por Pagar', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.1'],

            ['codigo' => '2.1.2', 'nombre' => 'OBLIGACIONES FISCALES Y SEGURIDAD SOCIAL', 'nivel' => 3, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => '2.1'],
            ['codigo' => '2.1.2.01', 'nombre' => 'Débito Fiscal IVA (13% Ley 843)', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.2'],
            ['codigo' => '2.1.2.02', 'nombre' => 'Retenciones Aporte Laboral Gestora Pública (12.71%)', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.2'],
            ['codigo' => '2.1.2.03', 'nombre' => 'Aportes Patronales por Pagar (CNS 10% + Gestora 5%)', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.2'],
            ['codigo' => '2.1.2.04', 'nombre' => 'Retenciones RC-IVA Régimen Complementario', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.2'],
            ['codigo' => '2.1.2.05', 'nombre' => 'Retenciones Judiciales y Descuentos por Atrasos', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.2'],

            ['codigo' => '2.1.3', 'nombre' => 'RECAUDACIONES POR CUENTA DE TERCEROS', 'nivel' => 3, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => '2.1'],
            ['codigo' => '2.1.3.01', 'nombre' => 'Tasa de Aseo Urbano Municipal por Pagar', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.1.3'],

            ['codigo' => '2.2', 'nombre' => 'PASIVO NO CORRIENTE', 'nivel' => 2, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => '2'],
            ['codigo' => '2.2.1', 'nombre' => 'PREVISIONES SOCIALES A LARGO PLAZO', 'nivel' => 3, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => false, 'padre' => '2.2'],
            ['codigo' => '2.2.1.01', 'nombre' => 'Previsión para Indemnizaciones por Años de Servicio', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PASIVO', 'permite' => true, 'padre' => '2.2.1'],

            // 3. PATRIMONIO
            ['codigo' => '3.1', 'nombre' => 'Patrimonio Público Institucional', 'nivel' => 2, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PATRIMONIO', 'permite' => true, 'padre' => '3'],
            ['codigo' => '3.2', 'nombre' => 'Resultados Acumulados de Gestiones Anteriores', 'nivel' => 2, 'naturaleza' => 'ACREEDORA', 'tipo' => 'PATRIMONIO', 'permite' => true, 'padre' => '3'],

            // 5. RECURSOS / INGRESOS
            ['codigo' => '5.1', 'nombre' => 'RECURSOS DE OPERACIÓN DEL SERVICIO', 'nivel' => 2, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => false, 'padre' => '5'],
            ['codigo' => '5.1.1', 'nombre' => 'Ventas por Tarifas de Agua y Alcantarillado', 'nivel' => 3, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => false, 'padre' => '5.1'],
            ['codigo' => '5.1.1.01', 'nombre' => 'Ingresos por Suministro de Agua Potable', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => true, 'padre' => '5.1.1'],
            ['codigo' => '5.1.1.02', 'nombre' => 'Ingresos por Servicio de Alcantarillado', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => true, 'padre' => '5.1.1'],

            ['codigo' => '5.1.2', 'nombre' => 'Otros Ingresos y Servicios Conexos', 'nivel' => 3, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => false, 'padre' => '5.1'],
            ['codigo' => '5.1.2.01', 'nombre' => 'Ingresos por Cortes y Reconexiones de Servicio', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => true, 'padre' => '5.1.2'],
            ['codigo' => '5.1.2.02', 'nombre' => 'Ingresos por Reposición y Cambio de Medidores', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => true, 'padre' => '5.1.2'],
            ['codigo' => '5.1.2.03', 'nombre' => 'Ingresos por Multas, Recargos e Intereses', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => true, 'padre' => '5.1.2'],
            ['codigo' => '5.1.2.04', 'nombre' => 'Ingresos por Formularios y Trámites Nuevos', 'nivel' => 4, 'naturaleza' => 'ACREEDORA', 'tipo' => 'RECURSO', 'permite' => true, 'padre' => '5.1.2'],

            // 6. GASTOS / EGRESOS
            ['codigo' => '6.1', 'nombre' => 'GASTOS DE FUNCIONAMIENTO Y OPERACIÓN', 'nivel' => 2, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => false, 'padre' => '6'],
            ['codigo' => '6.1.1', 'nombre' => 'SERVICIOS PERSONALES (PLANILLA DE SUELDOS)', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => false, 'padre' => '6.1'],
            ['codigo' => '6.1.1.01', 'nombre' => 'Sueldos Básicos del Personal', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.02', 'nombre' => 'Bono de Antigüedad DS 21060', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.03', 'nombre' => 'Asignación por Refrigerios', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.05', 'nombre' => 'Aporte Patronal Seguro de Salud (CNS 10%)', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.06', 'nombre' => 'Aporte Patronal Solidario Gestora (3%)', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.07', 'nombre' => 'Aporte Patronal Pro-Vivienda (2%)', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.08', 'nombre' => 'Provisión para Aguinaldo de Navidad (8.33%)', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],
            ['codigo' => '6.1.1.09', 'nombre' => 'Previsión para Indemnización por Despido/Retiro (8.33%)', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.1'],

            ['codigo' => '6.1.2', 'nombre' => 'MATERIALES, SUMINISTROS Y SERVICIOS BÁSICOS', 'nivel' => 3, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => false, 'padre' => '6.1'],
            ['codigo' => '6.1.2.01', 'nombre' => 'Energía Eléctrica para Bombeo de Pozos (DELAPAZ)', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.2'],
            ['codigo' => '6.1.2.02', 'nombre' => 'Sustancias Químicas y Cloro para Potabilización', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.2'],
            ['codigo' => '6.1.2.03', 'nombre' => 'Materiales de Fontanería y Reparación de Fugas', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.2'],
            ['codigo' => '6.1.2.04', 'nombre' => 'Gastos Operativos Menores de Caja Chica', 'nivel' => 4, 'naturaleza' => 'DEUDORA', 'tipo' => 'GASTO', 'permite' => true, 'padre' => '6.1.2'],
        ];

        // Mapeo id temporal para padres
        $idsPorCodigo = [];

        foreach ($cuentas as $c) {
            $padreId = null;
            if ($c['padre'] && isset($idsPorCodigo[$c['padre']])) {
                $padreId = $idsPorCodigo[$c['padre']];
            }

            $model = PlanCuenta::updateOrCreate(
                ['codigo' => $c['codigo']],
                [
                    'nombre' => $c['nombre'],
                    'nivel' => $c['nivel'],
                    'naturaleza' => $c['naturaleza'],
                    'tipo' => $c['tipo'],
                    'permite_movimiento' => $c['permite'],
                    'id_cuenta_padre' => $padreId,
                    'estado' => 'ACTIVO',
                ]
            );

            $idsPorCodigo[$c['codigo']] = $model->id;
        }

        // 4. Parámetros de Enlace Automático (Mapeo de Cuentas)
        $mapeos = [
            // COMERCIAL Y CAJAS
            [
                'codigo_enlace' => 'CAJA_CENTRAL_EFECTIVO',
                'descripcion' => 'Cuenta de ingreso de efectivo físico en caja recaudadora',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '1.1.1.01.001',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'BANCO_UNION_QR',
                'descripcion' => 'Cuenta bancaria fiscal para cobros por QR y transferencias',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '1.1.1.02.002',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'CUENTAS_COBRAR_AGUA',
                'descripcion' => 'Cuenta por cobrar de abonados por servicio de agua y alcantarillado',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '1.1.2.01.001',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'CUENTAS_COBRAR_CONVENIOS',
                'descripcion' => 'Cuenta por cobrar de cuotas de convenios de pago',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '1.1.2.01.003',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'INGRESO_AGUA_POTABLE',
                'descripcion' => 'Recurso por venta de agua potable residencial y comercial',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '5.1.1.01',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'INGRESO_ALCANTARILLADO',
                'descripcion' => 'Recurso por servicio de alcantarillado sanitario',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '5.1.1.02',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'INGRESO_RECONEXION_MULTAS',
                'descripcion' => 'Recurso por reconexión de servicio cortado y multas',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '5.1.2.01',
                'centro' => $ccComercial?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_CAJA_CHICA',
                'descripcion' => 'Cuenta contable para egresos menores de gaveta / caja chica',
                'modulo' => 'COMERCIAL',
                'cuenta_codigo' => '6.1.2.04',
                'centro' => $ccComercial?->id,
            ],

            // FACTURACIÓN SIAT
            [
                'codigo_enlace' => 'DEBITO_FISCAL_IVA',
                'descripcion' => 'Pasivo fiscal por IVA 13% sobre servicios facturados',
                'modulo' => 'FACTURACION',
                'cuenta_codigo' => '2.1.2.01',
                'centro' => $ccAdmin?->id,
            ],

            // RRHH Y PLANILLAS
            [
                'codigo_enlace' => 'GASTO_SUELDOS_BASICOS',
                'descripcion' => 'Gasto de sueldos básicos de la planilla salarial',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.01',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_BONO_ANTIGUEDAD',
                'descripcion' => 'Gasto por bono de antigüedad legal del personal',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.02',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_REFRIGERIOS',
                'descripcion' => 'Gasto por asignación de refrigerio diario de asistencia',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.03',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_PATRONAL_CNS',
                'descripcion' => 'Aporte patronal seguro de salud (CNS 10%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.05',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_PATRONAL_SOLIDARIO',
                'descripcion' => 'Aporte patronal solidario Gestora Pública (3%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.06',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_PATRONAL_VIVIENDA',
                'descripcion' => 'Aporte patronal pro-vivienda Gestora Pública (2%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.07',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_PROVISION_AGUINALDO',
                'descripcion' => 'Gasto mensual estimado para aguinaldo de navidad (8.33%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.08',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'GASTO_PREVISION_INDEMNIZACION',
                'descripcion' => 'Gasto mensual estimado para indemnización por retiro (8.33%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '6.1.1.09',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'SUELDOS_POR_PAGAR',
                'descripcion' => 'Pasivo de líquido pagable a funcionarios de EMAPAP',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '2.1.1.01',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'RETENCIONES_GESTORA_LABORAL',
                'descripcion' => 'Pasivo por retención laboral Gestora Pública (12.71%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '2.1.2.02',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'APORTES_PATRONALES_POR_PAGAR',
                'descripcion' => 'Pasivo por aportes patronales devengados (CNS 10% + Gestora 5%)',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '2.1.2.03',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'DESCUENTOS_ATRASOS_RETENCIONES',
                'descripcion' => 'Retenciones por atrasos y sanciones para fondos de bienestar',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '2.1.2.05',
                'centro' => $ccAdmin?->id,
            ],
            [
                'codigo_enlace' => 'PREVISION_INDEMNIZACION_PASIVO',
                'descripcion' => 'Pasivo no corriente para pago futuro de indemnizaciones',
                'modulo' => 'RRHH',
                'cuenta_codigo' => '2.2.1.01',
                'centro' => $ccAdmin?->id,
            ],
        ];

        foreach ($mapeos as $m) {
            $cuentaId = $idsPorCodigo[$m['cuenta_codigo']] ?? null;
            if ($cuentaId) {
                MapeoEnlace::updateOrCreate(
                    ['codigo_enlace' => $m['codigo_enlace']],
                    [
                        'descripcion' => $m['descripcion'],
                        'modulo' => $m['modulo'],
                        'id_cuenta_defecto' => $cuentaId,
                        'id_centro_costo_defecto' => $m['centro'],
                    ]
                );
            }
        }
    }
}
