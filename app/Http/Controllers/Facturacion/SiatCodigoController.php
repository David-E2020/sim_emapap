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
        $cuis = 'CUIS_EMAPA_DEMO';
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
        $sucursales = SiatSucursal::with(['puntosVenta', 'cufd' => function ($q) {
            $q->where('fecha_vigencia', '>', Carbon::now())->latest('id');
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $sucursales,
            'message' => 'Sucursales obtenidas exitosamente',
        ], Response::HTTP_OK);
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
        $cuisActivo = SiatCuis::where('id_sucursal', $sucursal->id)->latest('id')->first();
        $cuis = $cuisActivo ? $cuisActivo->codigo_cuis : 'CUIS_EMAPAP_DEFAULT';

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
        $cuisActivo = SiatCuis::where('id_sucursal', $puntoVenta->id_sucursal)->latest('id')->first();
        $cuis = $cuisActivo ? $cuisActivo->codigo_cuis : 'CUIS_EMAPAP_DEFAULT';

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

        return response()->json([
            'success' => false,
            'message' => 'No se pudo obtener el CUIS del SIN.',
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
