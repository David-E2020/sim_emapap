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
                'cert_path_absoluto' => $certPath,
                'ambiente_descripcion' => $config->codigo_ambiente === 1 ? 'PRODUCCIÓN OFICIAL' : 'PRUEBAS / PILOTO',
                'modalidad_descripcion' => $config->codigo_modalidad === 1 ? 'ELECTRÓNICA EN LÍNEA (CON FIRMA ADSIB)' : 'COMPUTARIZADA EN LÍNEA',
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
            'archivo_p12' => 'nullable|file|max:10240',
            'certificado_p12' => 'nullable|file|max:10240',
            'archivo_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
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

        $config->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Configuración institucional y parámetros SIAT actualizados exitosamente.',
            'mensaje' => 'Configuración institucional y parámetros SIAT actualizados exitosamente.',
            'data' => $config->fresh(),
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

        try {
            $soapService = new SiatSoapService();
            $resultado = $soapService->verificarComunicacion();
            $tiempoMs = round((microtime(true) - $inicio) * 1000, 2);

            $ambienteNombre = $ambienteTarget === 1 ? 'PRODUCCIÓN OFICIAL' : 'PRUEBAS / PILOTO';

            return response()->json([
                'success' => $resultado['success'] ?? false,
                'ambiente' => $ambienteTarget,
                'ambiente_nombre' => $ambienteNombre,
                'codigo_ambiente' => $ambienteTarget,
                'tiempo_ms' => $tiempoMs,
                'tiempo_respuesta_ms' => $tiempoMs,
                'endpoint' => $endpoint,
                'codigo_transaccion' => $resultado['codigo'] ?? null,
                'mensajes' => $resultado['mensajes'] ?? 'Comunicación exitosa',
                'message' => $resultado['mensajes'] ?? 'Comunicación procesada con el SIN',
                'mensaje' => $resultado['mensajes'] ?? 'Comunicación procesada con el SIN',
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
