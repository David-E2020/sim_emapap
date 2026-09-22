<?php

declare(strict_types=1);

namespace App\Services\Administracion;

use App\Models\Parametrica;
use App\Services\Audit\AuditService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ParametricaService
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    /**
     * Diccionario maestro de clasificación y encapsulamiento por módulo para cada catálogo paramétrico.
     */
    public static function getMetadataTablas(): array
    {
        return [
            // GESTIÓN COMERCIAL
            'TABLA_COMERCIAL_ESTADOS_ABONADO' => [
                'modulo' => 'COMERCIAL',
                'modulo_label' => 'Gestión Comercial',
                'modulo_icono' => 'mdi-water-pump',
                'modulo_color' => 'primary',
                'nombre_amigable' => 'Estados de Servicio del Abonado',
                'descripcion_uso' => 'Define los estados operativos del suministro (Activo, Corte, Suspendido, Permiso).',
                'vistas_asociadas' => ['Padrón de Abonados', 'Cortes y Reconexiones', 'Toma de Lecturas'],
            ],
            'TABLA_COMERCIAL_CONCEPTOS_OTROS_INGRESOS' => [
                'modulo' => 'COMERCIAL',
                'modulo_label' => 'Gestión Comercial',
                'modulo_icono' => 'mdi-water-pump',
                'modulo_color' => 'primary',
                'nombre_amigable' => 'Conceptos de Otros Ingresos y Servicios',
                'descripcion_uso' => 'Catálogo de cobros no tarifarios: Reconexión, Multas, Cambio de Nombre, Venta de Agua, etc.',
                'vistas_asociadas' => ['Caja y Cobranzas', 'Facturación SIAT'],
            ],
            'TABLA_COMERCIAL_TIPOS_MEDIDOR' => [
                'modulo' => 'COMERCIAL',
                'modulo_label' => 'Gestión Comercial',
                'modulo_icono' => 'mdi-water-pump',
                'modulo_color' => 'primary',
                'nombre_amigable' => 'Tipos y Diámetros de Medidores',
                'descripcion_uso' => 'Especificaciones técnicas de los medidores de agua instalados (1/2", 3/4", 1").',
                'vistas_asociadas' => ['Padrón de Abonados'],
            ],

            // RECURSOS HUMANOS
            'TABLA_RRHH_GENERO' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Género / Identidad Sexual',
                'descripcion_uso' => 'Opciones de género para el registro de personal.',
                'vistas_asociadas' => ['Ficha del Personal'],
            ],
            'TABLA_RRHH_ESTADO_CIVIL' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Estados Civiles',
                'descripcion_uso' => 'Estado civil legal del personal de EMAPAP.',
                'vistas_asociadas' => ['Ficha del Personal', 'Planillas'],
            ],
            'TABLA_RRHH_TIPO_CONTRATO' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Modalidades de Contratación',
                'descripcion_uso' => 'Tipos de relación laboral: Planta, Eventual, Consultoría.',
                'vistas_asociadas' => ['Contratos', 'Planillas'],
            ],
            'TABLA_RRHH_TIPO_JORNADA' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Jornadas y Horarios Laborales',
                'descripcion_uso' => 'Tipos de turnos: Completo, Medio tiempo, Turnos rotativos.',
                'vistas_asociadas' => ['Control de Asistencia', 'Horarios'],
            ],
            'TABLA_RRHH_NIVEL_INSTRUCCION' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Grados Académicos e Instrucción',
                'descripcion_uso' => 'Niveles educativos alcanzados por el personal.',
                'vistas_asociadas' => ['Ficha del Personal', 'Legajos'],
            ],
            'TABLA_RRHH_TIPO_DOCUMENTO_LEGAJO' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Documentación de Legajo Personal',
                'descripcion_uso' => 'Tipos de respaldos documentales (Títulos, Cédula, Certificados).',
                'vistas_asociadas' => ['Legajos Digitales'],
            ],
            'TABLA_RRHH_EXPEDIDO_DOC' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Departamentos de Expedición CI',
                'descripcion_uso' => 'Siglas de expedición de cédulas de identidad (LP, CB, SC, OR, etc.).',
                'vistas_asociadas' => ['Personal', 'Abonados'],
            ],
            'TABLA_RRHH_CONFIGURACION_SALARIAL' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Parámetros Salariales y Descuentos',
                'descripcion_uso' => 'Porcentajes AFP, Aporte Solidario y Salario Mínimo Nacional.',
                'vistas_asociadas' => ['Planillas de Sueldos'],
            ],
            'TABLA_RRHH_BONO_ANTIGUEDAD' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Escala de Bono de Antigüedad',
                'descripcion_uso' => 'Porcentajes legales vigentes por años de servicio institucional.',
                'vistas_asociadas' => ['Planillas de Sueldos'],
            ],
            'TABLA_RRHH_MODELO_BIOMETRICO' => [
                'modulo' => 'RRHH',
                'modulo_label' => 'Recursos Humanos',
                'modulo_icono' => 'mdi-account-group',
                'modulo_color' => 'teal',
                'nombre_amigable' => 'Modelos y Marcas de Biométricos',
                'descripcion_uso' => 'Dispositivos de marcación y control de asistencia física.',
                'vistas_asociadas' => ['Reloj Biométrico'],
            ],

            // CORRESPONDENCIA Y TRÁMITES
            'TABLA_CORRESPONDENCIA_TIPOS_DOCUMENTO' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Tipos de Documentos Oficiales (CITE)',
                'descripcion_uso' => 'Clasificación de documentos emitidos: Carta, Memorándum, Informe, Oficio.',
                'vistas_asociadas' => ['Generador de Documentos', 'Hojas de Ruta'],
            ],
            'TABLA_CORRESPONDENCIA_PRIORIDADES' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Niveles de Prioridad y Urgencia',
                'descripcion_uso' => 'Urgencia de atención de trámites: Normal, Urgente, Muy Urgente.',
                'vistas_asociadas' => ['Hojas de Ruta', 'Ventanilla Única'],
            ],
            'TABLA_CORRESPONDENCIA_PROVEIDOS' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Instrucciones y Proveídos Predefinidos',
                'descripcion_uso' => 'Acciones de derivación: Para su conocimiento, Atender urgentemente, Archivo.',
                'vistas_asociadas' => ['Derivaciones', 'Hojas de Ruta'],
            ],
            'TABLA_CORRESPONDENCIA_TIPOS_DESPACHO' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Tipos de Despacho y Envío',
                'descripcion_uso' => 'Vías de despacho: Mensajería Interna, Correo Certificado, Mano Propia.',
                'vistas_asociadas' => ['Despacho de Correspondencia'],
            ],
            'TABLA_CORRESPONDENCIA_ESTADOS_DESPACHO' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Estados de Entrega de Despachos',
                'descripcion_uso' => 'Seguimiento de envíos: Pendiente, En tránsito, Entregado, Observado.',
                'vistas_asociadas' => ['Despacho de Correspondencia'],
            ],
            'TABLA_CORRESPONDENCIA_ROLES_PARTICIPANTE' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Roles en Flujos de Trámite',
                'descripcion_uso' => 'Participación en el trámite: Solicitante, Destinatario, Vía, Con Copia.',
                'vistas_asociadas' => ['Hojas de Ruta'],
            ],
            'TABLA_CORRESPONDENCIA_TIPOS_SOLICITUD' => [
                'modulo' => 'CORRESPONDENCIA',
                'modulo_label' => 'Correspondencia y Trámites',
                'modulo_icono' => 'mdi-email-seal',
                'modulo_color' => 'deep-orange',
                'nombre_amigable' => 'Tipos de Solicitudes y Reclamos',
                'descripcion_uso' => 'Clasificación de trámites externos ingresados por ventanilla.',
                'vistas_asociadas' => ['Ventanilla Única'],
            ],

            // GENERAL / SISTEMA
            'TABLA_TIPO_DOCUMENTO' => [
                'modulo' => 'GENERAL',
                'modulo_label' => 'General / Sistema',
                'modulo_icono' => 'mdi-card-account-details-outline',
                'modulo_color' => 'indigo',
                'nombre_amigable' => 'Tipos de Documento de Identidad',
                'descripcion_uso' => 'Documentos de identidad nacional y tributarios: CI, NIT, Pasaporte, CEX.',
                'vistas_asociadas' => ['Usuarios', 'Abonados', 'Personal', 'Facturación SIAT'],
            ],
            'TABLA_TIPO_MONEDA' => [
                'modulo' => 'GENERAL',
                'modulo_label' => 'General / Sistema',
                'modulo_icono' => 'mdi-currency-usd',
                'modulo_color' => 'indigo',
                'nombre_amigable' => 'Tipos de Moneda Oficial',
                'descripcion_uso' => 'Monedas para transacciones: Bolivianos (BOB), Dólares (USD), UFV.',
                'vistas_asociadas' => ['Contabilidad', 'Caja y Cobranzas', 'Facturación SIAT'],
            ],
        ];
    }

    /**
     * Obtiene las tablas origen enriquecidas con metadatos de módulo y conteo optimizado.
     */
    public function getOriginTables(): Collection
    {
        $metadata = self::getMetadataTablas();

        $tables = Parametrica::query()
            ->select(['id', 'param_nombre', 'param_codigo', 'param_descripcion', 'param_tabla', 'param_valor', 'param_estado'])
            ->where('param_codigo', 'ORIGEN')
            ->where('param_valor', 0)
            ->where('param_estado', 'A')
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('parametricas as sub')
                    ->whereColumn('sub.param_tabla', 'parametricas.param_tabla')
                    ->where('sub.param_estado', 'A')
                    ->where('sub.param_valor', '>', 0);
            }, 'param_valor_contador')
            ->orderBy('param_tabla', 'ASC')
            ->get();

        $tables->each(function (Parametrica $item) use ($metadata) {
            $key = $item->param_tabla;
            $meta = $metadata[$key] ?? null;

            if ($meta) {
                $item->modulo = $meta['modulo'];
                $item->modulo_label = $meta['modulo_label'];
                $item->modulo_icono = $meta['modulo_icono'];
                $item->modulo_color = $meta['modulo_color'];
                $item->nombre_amigable = $meta['nombre_amigable'];
                $item->descripcion_uso = $meta['descripcion_uso'];
                $item->vistas_asociadas = $meta['vistas_asociadas'];
            } else {
                // Auto-clasificación heurística por prefijo si es una tabla nueva o no catalogada
                if (str_starts_with($key, 'TABLA_COMERCIAL_')) {
                    $item->modulo = 'COMERCIAL';
                    $item->modulo_label = 'Gestión Comercial';
                    $item->modulo_icono = 'mdi-water-pump';
                    $item->modulo_color = 'primary';
                } elseif (str_starts_with($key, 'TABLA_RRHH_')) {
                    $item->modulo = 'RRHH';
                    $item->modulo_label = 'Recursos Humanos';
                    $item->modulo_icono = 'mdi-account-group';
                    $item->modulo_color = 'teal';
                } elseif (str_starts_with($key, 'TABLA_CORRESPONDENCIA_')) {
                    $item->modulo = 'CORRESPONDENCIA';
                    $item->modulo_label = 'Correspondencia y Trámites';
                    $item->modulo_icono = 'mdi-email-seal';
                    $item->modulo_color = 'deep-orange';
                } elseif (str_starts_with($key, 'TABLA_FACTURACION_')) {
                    $item->modulo = 'FACTURACION';
                    $item->modulo_label = 'Facturación SIAT';
                    $item->modulo_icono = 'mdi-receipt-text';
                    $item->modulo_color = 'purple';
                } else {
                    $item->modulo = 'GENERAL';
                    $item->modulo_label = 'General / Sistema';
                    $item->modulo_icono = 'mdi-cog';
                    $item->modulo_color = 'indigo';
                }

                $item->nombre_amigable = $item->param_nombre;
                $item->descripcion_uso = $item->param_descripcion ?: 'Catálogo de opciones configurables del sistema.';
                $item->vistas_asociadas = ['Sistema'];
            }
        });

        return $tables;
    }

    /**
     * Obtiene los campos específicos de una tabla paramétrica con conteo optimizado.
     */
    public function getFieldsByTable(string $paramTabla): Collection
    {
        return Parametrica::query()
            ->select(['id', 'param_nombre', 'param_codigo', 'param_descripcion', 'param_tabla', 'param_valor', 'param_estado'])
            ->where('param_estado', 'A')
            ->where('param_tabla', $paramTabla)
            ->where('param_valor', '>', 0)
            ->selectSub(function ($query) {
                $query->selectRaw('count(*)')
                    ->from('parametricas as sub')
                    ->whereColumn('sub.param_tabla', 'parametricas.param_tabla')
                    ->where('sub.param_estado', 'A');
            }, 'param_valor_contador')
            ->orderBy('id', 'ASC')
            ->get();
    }

    /**
     * Registra o actualiza una tabla o campo paramétrico de forma transaccional.
     */
    public function save(array $data, ?int $userId = null): Parametrica
    {
        return DB::transaction(function () use ($data, $userId) {
            $userId = $userId ?? auth()->id();
            $isNew = empty($data['id']);

            if (! $isNew) {
                $parametrica = Parametrica::findOrFail((int) $data['id']);
                $oldValues = $parametrica->only(['param_nombre', 'param_descripcion', 'param_tabla', 'param_valor']);
            } else {
                $parametrica = new Parametrica;
                $oldValues = null;
            }

            $parametrica->param_tabla = strtoupper(trim($data['param_tabla'] ?? ''));
            $parametrica->param_nombre = strtoupper(trim($data['param_nombre'] ?? ''));
            $parametrica->param_descripcion = isset($data['param_descripcion']) ? strtoupper(trim($data['param_descripcion'])) : null;
            $parametrica->param_codigo = $data['param_codigo'] ?? 'ORIGEN';
            $parametrica->param_valor = (int) ($data['param_valor'] ?? 0);
            $parametrica->param_estado = 'A';

            if ($isNew) {
                $parametrica->param_usr_registrado = $userId;
            } else {
                $parametrica->param_usr_modificado = $userId;
            }

            $parametrica->save();

            // Auditoría inmutable
            $this->auditService->log(
                event: $isNew ? 'parametrica_created' : 'parametrica_updated',
                model: $parametrica,
                oldValues: $oldValues,
                newValues: $parametrica->only(['param_nombre', 'param_descripcion', 'param_tabla', 'param_valor']),
                userId: $userId
            );

            return $parametrica;
        });
    }

    /**
     * Elimina lógicamente una paramétrica validando dependencias hijo.
     */
    public function delete(int $id, ?int $userId = null): Parametrica
    {
        return DB::transaction(function () use ($id, $userId) {
            $userId = $userId ?? auth()->id();
            $parametrica = Parametrica::findOrFail($id);

            // Si es una tabla origen (valor 0), validar que no tenga campos hijos activos
            if ((int) $parametrica->param_valor === 0) {
                $hasActiveChildren = Parametrica::where('param_tabla', $parametrica->param_tabla)
                    ->where('param_valor', '<>', 0)
                    ->where('param_estado', 'A')
                    ->exists();

                if ($hasActiveChildren) {
                    throw new RuntimeException("No se puede eliminar la tabla '{$parametrica->param_tabla}' porque aún contiene subcampos activos asociados.");
                }
            }

            $oldValues = $parametrica->only(['param_estado', 'deleted_at']);

            $parametrica->param_estado = 'B';
            $parametrica->param_usr_eliminado = $userId;
            $parametrica->deleted_at = now();
            $parametrica->save();

            // Auditoría inmutable
            $this->auditService->log(
                event: 'parametrica_deleted',
                model: $parametrica,
                oldValues: $oldValues,
                newValues: ['param_estado' => 'B', 'deleted_at' => (string) now()],
                userId: $userId
            );

            return $parametrica;
        });
    }
}
