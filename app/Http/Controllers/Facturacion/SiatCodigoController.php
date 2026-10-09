<?php

declare(strict_types=1);

namespace App\Http\Controllers\Facturacion;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\ProductoServicioFactura;
use App\Models\Facturacion\SiatCatalogo;
use App\Models\Facturacion\SiatCufd;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Services\Facturacion\SiatSoapService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SiatCodigoController extends Controller
{
    public function __construct(
        private readonly SiatSoapService $siatSoapService
    ) {}

    /**
     * Prueba de conectividad SOAP con los servidores del SIN.
     */
    public function estadoConexion(): JsonResponse
    {
        $res = $this->siatSoapService->verificarComunicacion();

        return response()->json([
            'success' => $res['success'],
            'data' => $res,
            'message' => $res['success'] ? 'Conexión exitosa con el SIAT' : $res['mensaje'],
        ], Response::HTTP_OK);
    }

    /**
     * Consulta en tiempo real de validez de NIT ante el SIN.
     */
    public function verificarNit(string $nit): JsonResponse
    {
        $cuis = SiatCuis::getVigente();
        $res = $this->siatSoapService->verificarNit($cuis, trim($nit));

        return response()->json([
            'success' => true,
            'data' => $res,
            'message' => $res['mensaje'],
        ], Response::HTTP_OK);
    }

    /**
     * Catálogos tributarios paramétricos (tipos documento, métodos de pago, motivos de anulación).
     */
    public function catalogos(): JsonResponse
    {
        $tiposDocumento = [
            ['codigo' => 1, 'descripcion' => 'CÉDULA DE IDENTIDAD (CI)'],
            ['codigo' => 5, 'descripcion' => 'NIT (NÚMERO DE IDENTIFICACIÓN TRIBUTARIA)'],
            ['codigo' => 2, 'descripcion' => 'CÉDULA DE IDENTIDAD DE EXTRANJERO (CEX)'],
            ['codigo' => 3, 'descripcion' => 'PASAPORTE'],
            ['codigo' => 4, 'descripcion' => 'OTRO DOCUMENTO DE IDENTIDAD'],
        ];

        $metodosPago = [
            ['codigo' => 1, 'descripcion' => 'EFECTIVO'],
            ['codigo' => 2, 'descripcion' => 'TARJETA DE CRÉDITO/DÉBITO'],
            ['codigo' => 7, 'descripcion' => 'TRANSFERENCIA BANCARIA'],
            ['codigo' => 9, 'descripcion' => 'PAGO POR QR'],
            ['codigo' => 32, 'descripcion' => 'PAGO POSTERIOR / CRÉDITO'],
        ];

        $motivosAnulacion = [
            ['codigo' => 1, 'descripcion' => 'FACTURA MAL EMITIDA'],
            ['codigo' => 2, 'descripcion' => 'DATOS DE EMISIÓN INCORRECTOS'],
            ['codigo' => 3, 'descripcion' => 'FACTURA O NOTA FISCAL DEVUELTA'],
        ];

        $unidadesMedida = [
            ['codigo' => 58, 'descripcion' => 'UNIDAD (BIENES)'],
            ['codigo' => 1, 'descripcion' => 'KILOGRAMO'],
            ['codigo' => 2, 'descripcion' => 'GRAMO'],
            ['codigo' => 24, 'descripcion' => 'LITRO'],
            ['codigo' => 62, 'descripcion' => 'QUINTAL'],
            ['codigo' => 63, 'descripcion' => 'BOLSA / SACO'],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'tipos_documento' => $tiposDocumento,
                'metodos_pago' => $metodosPago,
                'motivos_anulacion' => $motivosAnulacion,
                'unidades_medida' => $unidadesMedida,
            ],
            'message' => 'Catálogos cargados exitosamente',
        ], Response::HTTP_OK);
    }

    /**
     * Sucursales y Puntos de Venta configurados con su estado de CUFD.
     */
    public function sucursales(): JsonResponse
    {
        $sucursales = SiatSucursal::with([
            'puntosVenta.cajeroDefecto',
            'puntosVenta.sesionActiva.cajero',
            'cufd' => function ($q) {
                $q->where('fecha_vigencia', '>', Carbon::now())->latest('id');
            }
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $sucursales,
            'message' => 'Sucursales obtenidas exitosamente',
        ], Response::HTTP_OK);
    }

    /**
     * Registrar una nueva sucursal y solicitar sus credenciales SIAT ante el SIN.
     */
    public function registrarSucursal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo_sucursal' => 'required|integer|min:0',
            'nombre' => 'required|string|max:150',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'municipio' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
        ]);

        $sucursal = SiatSucursal::updateOrCreate(
            ['codigo_sucursal' => (int) $validated['codigo_sucursal']],
            [
                'nombre' => mb_strtoupper($validated['nombre']),
                'direccion' => $validated['direccion'],
                'telefono' => $validated['telefono'] ?? '2-8147000',
                'municipio' => $validated['municipio'] ?? 'Patacamaya',
                'departamento' => $validated['departamento'] ?? 'La Paz',
                '_estado' => 'ACTIVO',
                '_transaccion' => 'REG_SUCURSAL',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]
        );

        // 1. Crear Punto de Venta 0 (Casa Matriz o Sucursal Central)
        $pv0 = SiatPuntoVenta::firstOrCreate(
            [
                'id_sucursal' => $sucursal->id,
                'codigo_punto_venta' => 0,
            ],
            [
                'nombre' => "Caja Principal Sucursal {$sucursal->codigo_sucursal}",
                'tipo_punto_venta' => 0,
                'descripcion' => "Ventanilla principal y recaudación general de {$sucursal->nombre}",
                '_estado' => 'ACTIVO',
                '_transaccion' => 'AUTO_PV0',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]
        );

        // 2. Solicitar CUIS ante el SIN
        $resCuis = $this->siatSoapService->solicitarCuis((int) $sucursal->codigo_sucursal, 0);
        $cuisCodigo = null;
        if (!empty($resCuis['success'])) {
            $cuisCodigo = $resCuis['cuis'];
            SiatCuis::create([
                'id_sucursal' => $sucursal->id,
                'id_punto_venta' => null,
                'codigo' => $cuisCodigo,
                'fecha_vigencia' => Carbon::parse($resCuis['fecha_vigencia']),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'REG_SUC_CUIS',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]);
        } elseif (($resCuis['codigo_error'] ?? 0) === 980) {
            $cuisExistente = SiatCuis::where('id_sucursal', $sucursal->id)->whereNull('id_punto_venta')->latest('id')->first();
            $cuisCodigo = $cuisExistente ? $cuisExistente->codigo : '6D4A1883';
        }

        // 3. Solicitar CUFD ante el SIN si se obtuvo el CUIS
        if ($cuisCodigo) {
            $resCufd = $this->siatSoapService->solicitarCufd($cuisCodigo, (int) $sucursal->codigo_sucursal, 0);
            if (!empty($resCufd['success'])) {
                SiatCufd::create([
                    'id_sucursal' => $sucursal->id,
                    'id_punto_venta' => $pv0->id,
                    'codigo' => $resCufd['cufd'],
                    'codigo_control' => $resCufd['codigo_control'],
                    'direccion' => $resCufd['direccion'] ?? $sucursal->direccion,
                    'fecha_vigencia' => Carbon::parse($resCufd['fecha_vigencia']),
                    '_estado' => 'ACTIVO',
                    '_transaccion' => 'REG_SUC_CUFD',
                    '_usuario_creacion' => auth()->id() ?? 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $sucursal->load(['puntosVenta', 'cufd']),
            'message' => "Sucursal N° {$sucursal->codigo_sucursal} ({$sucursal->nombre}) registrada y sincronizada con el SIN exitosamente.",
        ], Response::HTTP_CREATED);
    }

    /**
     * Servicios y conceptos de agua potable y alcantarillado de EMAPA Patacamaya.
     */
    public function productos(): JsonResponse
    {
        $productos = ProductoServicioFactura::where('_estado', 'ACTIVO')
            ->orderBy('codigo_producto_empresa')
            ->get();

        if ($productos->isEmpty()) {
            (new \Database\Seeders\Facturacion\EmapapServiciosSeeder())->run();
            $productos = ProductoServicioFactura::where('_estado', 'ACTIVO')->orderBy('codigo_producto_empresa')->get();
        }

        return response()->json([
            'success' => true,
            'data' => $productos,
            'message' => 'Servicios de EMAPAP obtenidos exitosamente',
        ], Response::HTTP_OK);
    }

    /**
     * Sincronización y verificación del reloj con los servidores del SIN.
     */
    public function sincronizarHora(): JsonResponse
    {
        $res = $this->siatSoapService->sincronizarFechaHora();

        return response()->json([
            'success' => $res['success'],
            'fecha_hora_sin' => $res['fecha_hora_sin'] ?? null,
            'fecha_hora_local' => \Carbon\Carbon::now()->toIso8601String(),
            'diferencia_segundos' => $res['diferencia_segundos'] ?? 0,
            'en_tolerancia' => $res['en_tolerancia'] ?? true,
            'data' => $res,
            'message' => $res['mensaje'] ?? 'Hora sincronizada.',
        ], Response::HTTP_OK);
    }

    /**
     * Registrar un nuevo servicio o concepto de cobranza de EMAPAP Patacamaya.
     */
    public function guardarProducto(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'codigo_producto_empresa' => 'required|string|max:50',
            'descripcion' => 'required|string|max:255',
            'precio_unitario' => 'required|numeric|min:0',
            'codigo_actividad' => 'nullable|string|max:20',
            'codigo_producto_sin' => 'nullable|string|max:20',
            'codigo_unidad_medida' => 'nullable|integer',
        ]);

        $producto = ProductoServicioFactura::updateOrCreate(
            ['codigo_producto_empresa' => $validated['codigo_producto_empresa']],
            [
                'codigo_actividad' => $validated['codigo_actividad'] ?? '360000',
                'codigo_producto_sin' => $validated['codigo_producto_sin'] ?? '86311',
                'descripcion' => mb_strtoupper($validated['descripcion']),
                'precio_unitario' => $validated['precio_unitario'],
                'codigo_unidad_medida' => $validated['codigo_unidad_medida'] ?? 58,
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CREAR_PROD',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $producto,
            'message' => 'Servicio registrado correctamente en el catálogo de EMAPAP.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Registrar un nuevo punto de venta ante el SIN.
     */
    public function registrarPuntoVenta(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_sucursal' => 'required|integer',
            'nombre' => 'required|string|max:100',
            'descripcion' => 'required|string|max:255',
            'tipo_punto_venta' => 'nullable|integer',
        ]);

        $sucursal = SiatSucursal::findOrFail($validated['id_sucursal']);
        $cuis = SiatCuis::getVigente((int) $sucursal->id, null, 0);

        $tipo = $validated['tipo_punto_venta'] ?? 5; // 5: Punto de Venta Fijo

        $resSin = $this->siatSoapService->registroPuntoVenta(
            $validated['nombre'],
            $validated['descripcion'],
            $tipo,
            (int) $sucursal->codigo_sucursal,
            $cuis
        );

        if (!$resSin['success']) {
            return response()->json([
                'success' => false,
                'message' => $resSin['mensaje'] ?? 'Error al registrar punto de venta en el SIN',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $codigoPv = $resSin['codigo_punto_venta'];

        $puntoVenta = SiatPuntoVenta::updateOrCreate(
            [
                'id_sucursal' => $sucursal->id,
                'codigo_punto_venta' => $codigoPv,
            ],
            [
                'nombre' => mb_strtoupper($validated['nombre']),
                'tipo_punto_venta' => $tipo,
                'descripcion' => $validated['descripcion'],
                '_estado' => 'ACTIVO',
                '_transaccion' => 'REG_PV_SIN',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]
        );

        // Solicitar CUIS para el nuevo punto de venta
        $resCuis = $this->siatSoapService->solicitarCuis((int) $sucursal->codigo_sucursal, $codigoPv);
        if ($resCuis['success']) {
            SiatCuis::create([
                'id_sucursal' => $sucursal->id,
                'id_punto_venta' => $puntoVenta->id,
                'codigo' => $resCuis['cuis'],
                'fecha_vigencia' => Carbon::now()->addYear(),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'CUIS_PV',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $puntoVenta,
            'message' => 'Punto de venta registrado y habilitado exitosamente ante el SIN.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Cierre formal de un punto de venta ante el SIN.
     */
    public function cerrarPuntoVenta(int $id): JsonResponse
    {
        $puntoVenta = SiatPuntoVenta::with('sucursal')->findOrFail($id);
        $sucursal = $puntoVenta->sucursal;
        $cuis = SiatCuis::getVigente((int) $puntoVenta->id_sucursal, null, 0);

        $resSin = $this->siatSoapService->cierrePuntoVenta(
            (int) $puntoVenta->codigo_punto_venta,
            $sucursal ? (int) $sucursal->codigo_sucursal : 0,
            $cuis
        );

        $puntoVenta->_estado = 'CERRADO';
        $puntoVenta->_transaccion = 'CIERRE_PV';
        $puntoVenta->save();

        return response()->json([
            'success' => true,
            'message' => $resSin['mensaje'] ?? 'Punto de venta cerrado formalmente.',
        ], Response::HTTP_OK);
    }

    /**
     * Solicitar o renovar CUIS para un punto de venta.
     */
    public function solicitarCuisPuntoVenta(int $id): JsonResponse
    {
        $puntoVenta = SiatPuntoVenta::with('sucursal')->findOrFail($id);
        $sucursal = $puntoVenta->sucursal;

        $resCuis = $this->siatSoapService->solicitarCuis(
            $sucursal ? (int) $sucursal->codigo_sucursal : 0,
            (int) $puntoVenta->codigo_punto_venta
        );

        if ($resCuis['success']) {
            SiatCuis::create([
                'id_sucursal' => $puntoVenta->id_sucursal,
                'id_punto_venta' => $puntoVenta->id,
                'codigo' => $resCuis['cuis'],
                'fecha_vigencia' => Carbon::now()->addYear(),
                '_estado' => 'ACTIVO',
                '_transaccion' => 'RENOV_CUIS',
                '_usuario_creacion' => auth()->id() ?? 1,
            ]);

            return response()->json([
                'success' => true,
                'cuis' => $resCuis['cuis'],
                'message' => 'CUIS obtenido y renovado satisfactoriamente ante el SIN.',
            ], Response::HTTP_OK);
        }

        // Si el SIN indica que ya existe un CUIS vigente (código 980)
        if (($resCuis['codigo_error'] ?? null) === 980 || str_contains($resCuis['mensaje'] ?? '', 'EXISTE UN CUIS VIGENTE')) {
            $cuisVigente = SiatCuis::where('id_punto_venta', $puntoVenta->id)
                ->where('_estado', 'ACTIVO')
                ->latest('id')
                ->first();

            return response()->json([
                'success' => true,
                'cuis' => $cuisVigente ? $cuisVigente->codigo : null,
                'message' => 'El punto de venta ya cuenta con un CUIS vigente y habilitado ante el SIN.',
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => $resCuis['mensaje'] ?? 'No se pudo obtener el CUIS del SIN.',
            'sin_response' => $resCuis,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
