<?php

declare(strict_types=1);

namespace App\Http\Controllers\Datos;

use App\Http\Controllers\Controller;
use App\Models\Facturacion\ConfiguracionEmpresa;
use App\Models\Facturacion\SiatCuis;
use App\Models\Facturacion\SiatCufd;
use App\Services\Facturacion\SiatSoapService;
use Carbon\Carbon;
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
                'clave' => 'servicio_basico',
                'nombre' => '1. Factura de Servicios Básicos (Oficial EMAPAP)',
                'descripcion' => 'WSDL principal para emisión y contingencia de agua potable y alcantarillado (Sector 13).',
                'url_piloto' => $endpointsPiloto['servicio_basico'] ?? 'https://pilotosiatservicios.impuestos.gob.bo/v2/ServicioFacturacionServicioBasico?wsdl',
                'url_produccion' => $endpointsProduccion['servicio_basico'] ?? 'https://siatrest.impuestos.gob.bo/v2/ServicioFacturacionServicioBasico?wsdl',
                'url_actual' => $endpointsActivos['servicio_basico'] ?? '',
                'es_oficial_emapa' => true,
            ],
            [
                'clave' => 'sincronizacion',
                'nombre' => '2. Facturación Sincronización',
                'descripcion' => 'Catálogos SIN, actividades económicas, productos/servicios homologados y reloj oficial.',
                'url_piloto' => $endpointsPiloto['sincronizacion'] ?? '',
                'url_produccion' => $endpointsProduccion['sincronizacion'] ?? '',
                'url_actual' => $endpointsActivos['sincronizacion'] ?? '',
                'es_oficial_emapa' => true,
            ],
            [
                'clave' => 'codigos',
                'nombre' => '3. Facturación Códigos',
                'descripcion' => 'Obtención y renovación periódica de credenciales CUIS (anual) y CUFD (diario).',
                'url_piloto' => $endpointsPiloto['codigos'] ?? '',
                'url_produccion' => $endpointsProduccion['codigos'] ?? '',
                'url_actual' => $endpointsActivos['codigos'] ?? '',
                'es_oficial_emapa' => true,
            ],
            [
                'clave' => 'operaciones',
                'nombre' => '4. Facturación Operaciones',
                'descripcion' => 'Puntos de venta y registro oficial de Eventos Significativos de Contingencia.',
                'url_piloto' => $endpointsPiloto['operaciones'] ?? '',
                'url_produccion' => $endpointsProduccion['operaciones'] ?? '',
                'url_actual' => $endpointsActivos['operaciones'] ?? '',
                'es_oficial_emapa' => true,
            ],
            [
                'clave' => 'compra_venta',
                'nombre' => 'Compra-Venta General (Sector 1 - General)',
                'descripcion' => 'Para comercio de bienes y tiendas ordinarias. No aplicable a servicios de agua potable.',
                'url_piloto' => $endpointsPiloto['compra_venta'] ?? '',
                'url_produccion' => $endpointsProduccion['compra_venta'] ?? '',
                'url_actual' => $endpointsActivos['compra_venta'] ?? '',
                'es_oficial_emapa' => false,
            ],
            [
                'clave' => 'computarizada',
                'nombre' => 'Modalidad Computarizada en Línea (Alternativa)',
                'descripcion' => 'Válido solo si no se usa firma digital ADSIB (EMAPAP está configurado en Electrónica).',
                'url_piloto' => $endpointsPiloto['computarizada'] ?? '',
                'url_produccion' => $endpointsProduccion['computarizada'] ?? '',
                'url_actual' => $endpointsActivos['computarizada'] ?? '',
                'es_oficial_emapa' => false,
            ],
            [
                'clave' => 'electronica',
                'nombre' => 'Electrónica en Línea Genérica (Antiguo/General)',
                'descripcion' => 'Endpoint genérico de compra-venta electrónica. En SIAT v2 para agua se usa el Servicio Básico.',
                'url_piloto' => $endpointsPiloto['electronica'] ?? '',
                'url_produccion' => $endpointsProduccion['electronica'] ?? '',
                'url_actual' => $endpointsActivos['electronica'] ?? '',
                'es_oficial_emapa' => false,
            ],
            [
                'clave' => 'qr',
                'nombre' => 'Portal de Verificación QR SIN (Consulta Ciudadana)',
                'descripcion' => 'Enlace web público para consulta de facturas al escanear el código QR del recibo.',
                'url_piloto' => $endpointsPiloto['qr'] ?? '',
                'url_produccion' => $endpointsProduccion['qr'] ?? '',
                'url_actual' => $endpointsActivos['qr'] ?? '',
                'es_oficial_emapa' => false,
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

        $nitFormulario = trim((string) $request->input('nit'));
        $codSistemaFormulario = trim((string) $request->input('codigo_sistema'));
        $modalidadFormulario = (int) $request->input('codigo_modalidad');
        $tokenInput = trim((string) ($request->input('token_delegado') ?? $config->token_delegado ?? ''));

        $modalidadAutoAjustada = null;
        $sistemaAutoAjustado = null;

        // 1. Validación Cruzada Token SIAT vs Identidad Institucional
        if (!empty($tokenInput)) {
            $tokenNit = SiatSoapService::extractNitFromToken($tokenInput);
            $tokenSistema = SiatSoapService::extractCodigoSistemaFromToken($tokenInput);
            $tokenSubsistema = SiatSoapService::extractSubsistemaFromToken($tokenInput);

            // Validar que el NIT de Identidad Institucional coincida con el NIT del Token
            if ($tokenNit && $nitFormulario && (string) $tokenNit !== (string) $nitFormulario) {
                return response()->json([
                    'success' => false,
                    'message' => "Discrepancia fiscal detectada: El NIT de Identidad Institucional ({$nitFormulario}) no coincide con el NIT delegado contenido en el Token SIAT ({$tokenNit}). Debe registrar el mismo NIT.",
                    'mensaje' => "Discrepancia fiscal detectada: El NIT de Identidad Institucional ({$nitFormulario}) no coincide con el NIT delegado contenido en el Token SIAT ({$tokenNit}). Debe registrar el mismo NIT.",
                    'token_nit' => $tokenNit,
                    'formulario_nit' => $nitFormulario,
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Si el código de sistema difiere del token, auto-sincronizarlo con el real del token
            if ($tokenSistema && $codSistemaFormulario !== $tokenSistema) {
                $sistemaAutoAjustado = "Código de Sistema sincronizado automáticamente con el Token ({$tokenSistema}).";
                $codSistemaFormulario = $tokenSistema;
            }

            // Si el subsistema del token define la modalidad, auto-ajustarla:
            // SFE => Electrónica en Línea (Modalidad 1), SFC => Computarizada en Línea (Modalidad 2)
            if ($tokenSubsistema === 'SFE' && $modalidadFormulario !== 1) {
                $modalidadFormulario = 1;
                $modalidadAutoAjustada = 'Modalidad autoajustada a "1 - Electrónica en Línea" según el Token (SFE).';
            } elseif ($tokenSubsistema === 'SFC' && $modalidadFormulario !== 2) {
                $modalidadFormulario = 2;
                $modalidadAutoAjustada = 'Modalidad autoajustada a "2 - Computarizada en Línea" según el Token (SFC).';
            }
        }

        $data = [
            'razon_social' => trim($request->input('razon_social')),
            'nombre_comercial' => trim((string) ($request->input('nombre_comercial') ?? $request->input('razon_social'))),
            'nit' => $nitFormulario,
            'telefono' => $request->input('telefono'),
            'correo' => $request->input('correo'),
            'direccion' => trim($request->input('direccion')),
            'municipio' => trim($request->input('municipio')),
            'codigo_ambiente' => (int) $request->input('codigo_ambiente'),
            'codigo_modalidad' => $modalidadFormulario,
            'codigo_sistema' => $codSistemaFormulario,
            '_usuario_modificacion' => auth()->id() ?? 1,
            '_fecha_modificacion' => now(),
            '_transaccion' => 'ACTUALIZAR',
        ];

        // 2. Actualizar Token Delegado si fue remitido
        if ($request->has('token_delegado')) {
            $data['token_delegado'] = $tokenInput;
        }

        // 3. Actualizar Contraseña del Certificado .p12 solo si se proveyó una nueva
        $passwordP12 = $request->input('password_p12') ?? $request->input('certificado_password');
        if (!empty($passwordP12)) {
            $data['password_p12'] = $passwordP12;
        }

        // 4. Procesar subida de Certificado Digital .p12
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

        // 5. Procesar subida de Logo Institucional
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

        // 6. Procesar personalización de Endpoints WSDL si fue remitida
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

        // 7. Diagnóstico y Sincronización en vivo con Impuestos Nacionales
        $diagnosticoSin = [
            'comunicacion_ok' => false,
            'nit_verificado' => null,
            'nit_mensaje' => null,
            'cuis' => null,
            'cufd' => null,
            'reloj_sin' => null,
            'catalogos_actualizados' => false,
            'modalidad_autoajustada' => $modalidadAutoAjustada,
            'sistema_autoajustado' => $sistemaAutoAjustado,
            'advertencias' => [],
        ];

        try {
            $overrides = [
                'nit' => $data['nit'],
                'codigo_sistema' => $data['codigo_sistema'],
                'modalidad' => $data['codigo_modalidad'],
                'ambiente' => $data['codigo_ambiente'],
                'token_delegado' => $data['token_delegado'] ?? $config->token_delegado,
            ];
            $soapService = new SiatSoapService($overrides);
            $resCom = $soapService->verificarComunicacion();

            if (!empty($resCom['success'])) {
                $diagnosticoSin['comunicacion_ok'] = true;

                // A. Sincronizar Fecha y Hora del SIN
                $resHora = $soapService->sincronizarFechaHora();
                if (!empty($resHora['success'])) {
                    $diagnosticoSin['reloj_sin'] = $resHora['fecha_hora'] ?? null;
                }

                // B. Solicitar y actualizar CUIS oficial Punto 0
                $resCuis = $soapService->solicitarCuis(0, 0);
                $cuisCodigo = null;

                if (!empty($resCuis['success']) && !empty($resCuis['cuis'])) {
                    $cuisCodigo = (string) $resCuis['cuis'];
                    $vigencia = !empty($resCuis['fecha_vigencia']) ? Carbon::parse($resCuis['fecha_vigencia']) : Carbon::now()->addYear();

                    SiatCuis::create([
                        'id_sucursal' => 1,
                        'id_punto_venta' => 1,
                        'codigo' => $cuisCodigo,
                        'fecha_vigencia' => $vigencia,
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CONFIG_EMPRESA',
                        '_usuario_creacion' => auth()->id() ?? 1,
                    ]);
                    SiatCuis::create([
                        'id_sucursal' => 1,
                        'id_punto_venta' => null,
                        'codigo' => $cuisCodigo,
                        'fecha_vigencia' => $vigencia,
                        '_estado' => 'ACTIVO',
                        '_transaccion' => 'CONFIG_EMPRESA',
                        '_usuario_creacion' => auth()->id() ?? 1,
                    ]);
                } elseif (($resCuis['codigo_error'] ?? null) === 980 || str_contains($resCuis['mensaje'] ?? '', 'EXISTE UN CUIS VIGENTE')) {
                    $cuisVigente = SiatCuis::where('id_sucursal', 1)->where('_estado', 'ACTIVO')->latest('id')->first();
                    $cuisCodigo = $cuisVigente?->codigo;
                }

                if ($cuisCodigo) {
                    $diagnosticoSin['cuis'] = $cuisCodigo;

                    // C. Solicitar y actualizar CUFD Diario para Punto 0
                    $resCufd = $soapService->solicitarCufd($cuisCodigo, 0, 0);
                    if (!empty($resCufd['success'])) {
                        $diagnosticoSin['cufd'] = $resCufd['cufd'] ?? null;
                        SiatCufd::create([
                            'id_sucursal' => 1,
                            'id_punto_venta' => 1,
                            'codigo' => $resCufd['cufd'],
                            'codigo_control' => $resCufd['codigo_control'],
                            'direccion' => $resCufd['direccion'] ?? $data['direccion'],
                            'fecha_vigencia' => Carbon::parse($resCufd['fecha_vigencia']),
                            '_estado' => 'ACTIVO',
                            '_transaccion' => 'CONFIG_EMPRESA',
                            '_usuario_creacion' => auth()->id() ?? 1,
                        ]);
                    }

                    // D. Verificar NIT ante el padrón del SIN
                    $resNit = $soapService->verificarNit($cuisCodigo, $data['nit'], 0);
                    $diagnosticoSin['nit_verificado'] = $resNit['valido'] ?? false;
                    $diagnosticoSin['nit_mensaje'] = $resNit['mensaje'] ?? ($resNit['valido'] ? 'NIT Activo en el Padrón del SIN' : 'NIT No válido');

                    // E. Sincronizar catálogo esencial (sectores autorizados)
                    try {
                        $resAct = $soapService->sincronizarParametricas('sincronizarListaActividadesDocumentoSector', $cuisCodigo, 0, 0);
                        if (!empty($resAct['success'])) {
                            $diagnosticoSin['catalogos_actualizados'] = true;
                        }
                    } catch (\Throwable $e) {}
                } else {
                    $diagnosticoSin['advertencias'][] = 'No se pudo obtener CUIS: ' . ($resCuis['mensaje'] ?? 'Respuesta no exitosa del SIN');
                }
            } else {
                $diagnosticoSin['advertencias'][] = 'No se pudo contactar al servidor del SIN: ' . ($resCom['mensaje'] ?? 'Error desconocido');
            }
        } catch (\Throwable $e) {
            $diagnosticoSin['advertencias'][] = 'Servidores del SIN no disponibles en este momento: ' . $e->getMessage();
        }

        return response()->json([
            'success' => true,
            'message' => 'Configuración guardada y sincronizada exitosamente con Impuestos Nacionales.',
            'mensaje' => 'Configuración guardada y sincronizada exitosamente con Impuestos Nacionales.',
            'data' => $config->fresh(),
            'diagnostico_sin' => $diagnosticoSin,
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
