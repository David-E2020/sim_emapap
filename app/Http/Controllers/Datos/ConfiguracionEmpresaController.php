<?php

declare(strict_types=1);

namespace App\Http\Controllers\Datos;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\ConfiguracionEmpresa;
use App\Services\Facturacion\SiatSoapService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ConfiguracionEmpresaController extends Controller
{
    /**
     * Obtiene los datos institucionales y de configuración SIAT activos.
     */
    public function obtener(): JsonResponse
    {
        $config = ConfiguracionEmpresa::getActiva();

        $certPath = $config->certificado_p12_path
            ? (str_starts_with($config->certificado_p12_path, '/') ? $config->certificado_p12_path : base_path($config->certificado_p12_path))
            : config('siat.cert_path');

        $certExiste = file_exists($certPath);
        $tienePassword = !empty($config->password_p12);

        $endpointsPiloto = config('siat.wsdl.piloto', []);
        $endpointsProduccion = config('siat.wsdl.produccion', []);
        $endpointsActivos = $config->getWsdlEndpoints();
        $endpointsPersonalizados = $config->endpoints_personalizados ?? [];

        $catalogoEndpoints = [
            [
                'clave' => 'sincronizacion',
                'nombre' => 'Facturación Sincronización',
                'descripcion' => 'Catálogos, actividades económicas, productos/servicios SIN, leyendas y reloj oficial.',
                'url_piloto' => $endpointsPiloto['sincronizacion'] ?? '',
                'url_produccion' => $endpointsProduccion['sincronizacion'] ?? '',
                'url_actual' => $endpointsActivos['sincronizacion'] ?? '',
            ],
            [
                'clave' => 'codigos',
                'nombre' => 'Facturación Códigos',
                'descripcion' => 'Obtención y renovación periódica de CUIS (Código Único) y CUFD (Diario).',
                'url_piloto' => $endpointsPiloto['codigos'] ?? '',
                'url_produccion' => $endpointsProduccion['codigos'] ?? '',
                'url_actual' => $endpointsActivos['codigos'] ?? '',
            ],
            [
                'clave' => 'operaciones',
                'nombre' => 'Facturación Operaciones',
                'descripcion' => 'Gestión de puntos de venta y registro de eventos significativos de contingencia.',
                'url_piloto' => $endpointsPiloto['operaciones'] ?? '',
                'url_produccion' => $endpointsProduccion['operaciones'] ?? '',
                'url_actual' => $endpointsActivos['operaciones'] ?? '',
            ],
            [
                'clave' => 'compra_venta',
                'nombre' => 'Servicio Facturación Compra-Venta General',
                'descripcion' => 'Recepción sincrónica/asincrónica, consulta y anulación ordinaria de facturas.',
                'url_piloto' => $endpointsPiloto['compra_venta'] ?? '',
                'url_produccion' => $endpointsProduccion['compra_venta'] ?? '',
                'url_actual' => $endpointsActivos['compra_venta'] ?? '',
            ],
            [
                'clave' => 'computarizada',
                'nombre' => 'Servicio Facturación Computarizada en Línea',
                'descripcion' => 'Emisión directa en modalidad computarizada mediante código de control y hash.',
                'url_piloto' => $endpointsPiloto['computarizada'] ?? '',
                'url_produccion' => $endpointsProduccion['computarizada'] ?? '',
                'url_actual' => $endpointsActivos['computarizada'] ?? '',
            ],
            [
                'clave' => 'electronica',
                'nombre' => 'Servicio Facturación Electrónica en Línea',
                'descripcion' => 'Emisión directa en modalidad electrónica con firma digital XMLDSig (.p12).',
                'url_piloto' => $endpointsPiloto['electronica'] ?? '',
                'url_produccion' => $endpointsProduccion['electronica'] ?? '',
                'url_actual' => $endpointsActivos['electronica'] ?? '',
            ],
            [
                'clave' => 'qr',
                'nombre' => 'Portal de Consulta QR Oficial del SIN',
                'descripcion' => 'Enlace base para validación en línea de facturas por parte de clientes y abonados.',
                'url_piloto' => $endpointsPiloto['qr'] ?? '',
                'url_produccion' => $endpointsProduccion['qr'] ?? '',
                'url_actual' => $endpointsActivos['qr'] ?? '',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $config->id,
                'razon_social' => $config->razon_social,
                'nombre_comercial' => $config->nombre_comercial,
                'nit' => $config->nit,
                'telefono' => $config->telefono,
                'correo' => $config->correo,
                'direccion' => $config->direccion,
                'municipio' => $config->municipio,
                'logo_path' => $config->logo_path,
                'logo_url' => $config->logo_path ? url($config->logo_path) : null,
                'codigo_ambiente' => (int) $config->codigo_ambiente,
                'codigo_modalidad' => (int) $config->codigo_modalidad,
                'codigo_sistema' => $config->codigo_sistema,
                'token_delegado' => $config->token_delegado,
                'certificado_p12_path' => $config->certificado_p12_path,
                'tiene_password_p12' => $tienePassword,
                'tiene_password' => $tienePassword,
                'certificado_existe' => $certExiste,
                'tiene_certificado' => $certExiste,
                'cert_path_absoluto' => $certExiste ? 'Almacenamiento Seguro del Sistema' : 'No configurado',
                'ambiente_descripcion' => $config->codigo_ambiente === 1 ? 'PRODUCCIÓN OFICIAL' : 'PRUEBAS / PILOTO',
                'modalidad_descripcion' => $config->codigo_modalidad === 1 ? 'ELECTRÓNICA EN LÍNEA (CON FIRMA ADSIB)' : 'COMPUTARIZADA EN LÍNEA',
                'catalogo_endpoints' => $catalogoEndpoints,
                'endpoints_activos' => $endpointsActivos,
                'endpoints_personalizados' => $endpointsPersonalizados,
                'tiene_endpoints_personalizados' => !empty($endpointsPersonalizados),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Actualiza los datos de la empresa, credenciales fiscales y procesa archivos (.p12 y Logo).
     */
    public function guardar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razon_social' => 'required|string|max:255',
            'nombre_comercial' => 'nullable|string|max:150',
            'nit' => 'required|string|max:30',
            'telefono' => 'nullable|string|max:50',
            'correo' => 'nullable|email|max:100',
            'direccion' => 'required|string|max:255',
            'municipio' => 'required|string|max:100',
            'codigo_ambiente' => 'required|in:1,2',
            'codigo_modalidad' => 'required|in:1,2',
            'codigo_sistema' => 'required|string|max:100',
            'token_delegado' => 'nullable|string',
            'password_p12' => 'nullable|string|max:150',
            'certificado_password' => 'nullable|string|max:150',
            'archivo_p12' => 'nullable|file|mimes:p12,pfx,bin|max:10240',
            'certificado_p12' => 'nullable|file|mimes:p12,pfx,bin|max:10240',
            'archivo_logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'mensaje' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $config = ConfiguracionEmpresa::getActiva();

        $data = [
            'razon_social' => trim($request->input('razon_social')),
            'nombre_comercial' => trim((string) ($request->input('nombre_comercial') ?? $request->input('razon_social'))),
            'nit' => trim($request->input('nit')),
            'telefono' => $request->input('telefono'),
            'correo' => $request->input('correo'),
            'direccion' => trim($request->input('direccion')),
            'municipio' => trim($request->input('municipio')),
            'codigo_ambiente' => (int) $request->input('codigo_ambiente'),
            'codigo_modalidad' => (int) $request->input('codigo_modalidad'),
            'codigo_sistema' => trim($request->input('codigo_sistema')),
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
            '_transaccion' => 'ACTUALIZAR',
        ];

        // 1. Actualizar Token Delegado si fue remitido
        if ($request->has('token_delegado')) {
            $data['token_delegado'] = trim((string) $request->input('token_delegado'));
        }

        // 2. Actualizar Contraseña del Certificado .p12 solo si se proveyó una nueva
        $passwordP12 = $request->input('password_p12') ?? $request->input('certificado_password');
        if (!empty($passwordP12)) {
            $data['password_p12'] = $passwordP12;
        }

        // 3. Procesar subida de Certificado Digital .p12
        $archivoCert = $request->file('archivo_p12') ?? $request->file('certificado_p12');
        if ($archivoCert) {
            $certDir = storage_path('app/siat/certs');
            if (!is_dir($certDir)) {
                mkdir($certDir, 0755, true);
            }
            $fileName = 'certificado_' . time() . '.p12';
            $archivoCert->move($certDir, $fileName);
            $data['certificado_p12_path'] = 'storage/app/siat/certs/' . $fileName;
        }

        // 4. Procesar subida de Logo Institucional
        $archivoLogo = $request->file('archivo_logo') ?? $request->file('logo');
        if ($archivoLogo) {
            $logoDir = public_path('images/logos');
            if (!is_dir($logoDir)) {
                mkdir($logoDir, 0755, true);
            }
            $logoName = 'logo_empresa_' . time() . '.' . $archivoLogo->getClientOriginalExtension();
            $archivoLogo->move($logoDir, $logoName);
            $data['logo_path'] = '/images/logos/' . $logoName;
        }

        // 5. Procesar personalización de Endpoints WSDL si fue remitida
        if ($request->has('endpoints_personalizados')) {
            $endpointsInput = $request->input('endpoints_personalizados');
            if (is_string($endpointsInput)) {
                $endpointsInput = json_decode($endpointsInput, true);
            }
            if (is_array($endpointsInput)) {
                $data['endpoints_personalizados'] = $endpointsInput;
            }
        }

        $config->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración institucional y parámetros SIAT actualizados exitosamente.',
            'mensaje' => 'Configuración institucional y parámetros SIAT actualizados exitosamente.',
            'data' => $config->fresh(),
        ], Response::HTTP_OK);
    }

    /**
     * Restablece todas las URLs de servicios web WSDL a los valores oficiales de Impuestos Nacionales.
     */
    public function restablecerEndpoints(): JsonResponse
    {
        $config = ConfiguracionEmpresa::getActiva();
        $config->endpoints_personalizados = null;
        $config->_usuario_modificacion = auth()->id() ?? 1;
        $config->_fecha_modificacion = now();
        $config->_transaccion = 'ACTUALIZAR';
        $config->save();

        return response()->json([
            'success' => true,
            'message' => 'Todas las URLs WSDL y portal QR han sido restablecidas a los estándares oficiales del SIN.',
            'endpoints_activos' => $config->getWsdlEndpoints(),
        ], Response::HTTP_OK);
    }

    /**
     * Prueba de comunicación en vivo con los servidores del SIN (Piloto / Producción).
     */
    public function probarConexion(Request $request): JsonResponse
    {
        $inicio = microtime(true);
        $config = ConfiguracionEmpresa::getActiva();

        $ambienteTarget = $request->has('codigo_ambiente') 
            ? (int) $request->input('codigo_ambiente') 
            : (int) $config->codigo_ambiente;

        $ambienteConfigKey = $ambienteTarget === 1 ? 'produccion' : 'piloto';
        $endpoint = config("siat.wsdl.{$ambienteConfigKey}.sincronizacion", 'https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionSincronizacion?wsdl');

        $overrides = [
            'ambiente' => $ambienteTarget,
        ];
        if ($request->filled('codigo_modalidad')) {
            $overrides['modalidad'] = (int) $request->input('codigo_modalidad');
        }
        if ($request->filled('nit')) {
            $overrides['nit'] = (string) $request->input('nit');
        }
        if ($request->filled('codigo_sistema')) {
            $overrides['codigo_sistema'] = (string) $request->input('codigo_sistema');
        }
        if ($request->filled('token_delegado')) {
            $overrides['token_delegado'] = (string) $request->input('token_delegado');
        }

        try {
            $soapService = new SiatSoapService($overrides);
            $resultado = $soapService->verificarComunicacion();
            $tiempoMs = round((microtime(true) - $inicio) * 1000, 2);

            $ambienteNombre = $ambienteTarget === 1 ? 'PRODUCCIÓN OFICIAL' : 'PRUEBAS / PILOTO';

            $mensajeFinal = $resultado['mensajes'] ?? ($resultado['mensaje'] ?? 'Comunicación procesada con el SIN');

            return response()->json([
                'success' => $resultado['success'] ?? false,
                'ambiente' => $ambienteTarget,
                'ambiente_nombre' => $ambienteNombre,
                'codigo_ambiente' => $ambienteTarget,
                'tiempo_ms' => $tiempoMs,
                'tiempo_respuesta_ms' => $tiempoMs,
                'endpoint' => $endpoint,
                'codigo_transaccion' => $resultado['codigo'] ?? null,
                'mensajes' => $mensajeFinal,
                'message' => $mensajeFinal,
                'mensaje' => $mensajeFinal,
                'detalle' => $resultado,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            $tiempoMs = round((microtime(true) - $inicio) * 1000, 2);

            return response()->json([
                'success' => false,
                'ambiente' => $ambienteTarget,
                'tiempo_ms' => $tiempoMs,
                'tiempo_respuesta_ms' => $tiempoMs,
                'endpoint' => $endpoint,
                'message' => 'Fallo de comunicación con los servidores del SIN: ' . $e->getMessage(),
                'mensaje' => 'Fallo de comunicación con los servidores del SIN: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], Response::HTTP_OK);
        }
    }
}
