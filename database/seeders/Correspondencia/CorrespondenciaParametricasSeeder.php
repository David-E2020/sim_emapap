<?php

declare(strict_types=1);

namespace Database\Seeders\Correspondencia;

use App\Models\Parametrica;
use Illuminate\Database\Seeder;

class CorrespondenciaParametricasSeeder extends Seeder
{
    public function run(): void
    {
        $grupos = [
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_PROVEIDOS',
                'nombre' => 'CORRESPONDENCIA - PROVEÍDOS OFICIALES',
                'descripcion' => 'Catálogo oficial de proveídos para hojas de ruta y derivaciones',
                'items' => [
                    ['codigo' => 'PROV_01', 'nombre' => 'PASE A SUS EFECTOS', 'descripcion' => 'Para prosecución de trámite según normativa vigente'],
                    ['codigo' => 'PROV_02', 'nombre' => 'PARA SU CONOCIMIENTO Y FINES CONSIGUIENTES', 'descripcion' => 'Para conocimiento institucional y archivo'],
                    ['codigo' => 'PROV_03', 'nombre' => 'PARA INFORME TÉCNICO CIRCUNSTANCIADO', 'descripcion' => 'Elaborar informe técnico pormenorizado de evaluación'],
                    ['codigo' => 'PROV_04', 'nombre' => 'PREPARAR RESPUESTA OFICIAL', 'descripcion' => 'Redactar proyecto de respuesta o nota oficial'],
                    ['codigo' => 'PROV_05', 'nombre' => 'PROCEDER SEGÚN REGLAMENTO', 'descripcion' => 'Aplicar normativa interna y reglamentos de EMAPA'],
                    ['codigo' => 'PROV_06', 'nombre' => 'ATENCIÓN URGENTE PRIORITARIA', 'descripcion' => 'Atención prioritaria en plazo máximo de 24 horas'],
                    ['codigo' => 'PROV_07', 'nombre' => 'ARCHIVAR EXPEDIENTE', 'descripcion' => 'Trámite concluido, remitir a archivo central'],
                    ['codigo' => 'PROV_08', 'nombre' => 'PARA SU ATENCIÓN Y RESPUESTA', 'descripcion' => 'Atención directa y contestación al solicitante'],
                    ['codigo' => 'PROV_09', 'nombre' => 'PARA REVISIÓN Y VISTO BUENO', 'descripcion' => 'Control de calidad previo a la firma'],
                    ['codigo' => 'PROV_10', 'nombre' => 'AGREGAR ANTECEDENTES', 'descripcion' => 'Anexar documentación respaldatoria al expediente principal'],
                ],
            ],
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_TIPOS_DOCUMENTO',
                'nombre' => 'CORRESPONDENCIA - TIPOS DOCUMENTALES',
                'descripcion' => 'Clasificación de documentos oficiales redactados en EMAPA',
                'items' => [
                    ['codigo' => 'MEM', 'nombre' => 'MEMORÁNDUM', 'descripcion' => 'Comunicación interna de cumplimiento obligatorio o comisión'],
                    ['codigo' => 'INF', 'nombre' => 'INFORME TÉCNICO', 'descripcion' => 'Documento técnico circunstanciado de análisis y recomendación'],
                    ['codigo' => 'NI', 'nombre' => 'NOTA INTERNA', 'descripcion' => 'Comunicación administrativa y requerimiento entre unidades'],
                    ['codigo' => 'CIR', 'nombre' => 'CIRCULAR GENERAL', 'descripcion' => 'Directriz o disposición de cumplimiento general institucional'],
                    ['codigo' => 'CAR', 'nombre' => 'CARTA EXTERNA', 'descripcion' => 'Nota oficial dirigida a ministerios o instituciones públicas/privadas'],
                    ['codigo' => 'RES', 'nombre' => 'RESOLUCIÓN ADMINISTRATIVA', 'descripcion' => 'Acto administrativo resolutivo emitido por autoridad competente'],
                ],
            ],
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_PRIORIDADES',
                'nombre' => 'CORRESPONDENCIA - NIVELES DE PRIORIDAD Y SLA',
                'descripcion' => 'Categorización de urgencia y tiempos de respuesta',
                'items' => [
                    ['codigo' => 'PRIOR_URGENTE', 'nombre' => 'URGENTE', 'descripcion' => 'Plazo perentorio de 24 horas (1 día)'],
                    ['codigo' => 'PRIOR_ALTA', 'nombre' => 'ALTA', 'descripcion' => 'Plazo máximo de 48 horas (2 días)'],
                    ['codigo' => 'PRIOR_MEDIA', 'nombre' => 'MEDIA', 'descripcion' => 'Plazo estándar de 72 horas (3 días)'],
                    ['codigo' => 'PRIOR_BAJA', 'nombre' => 'BAJA', 'descripcion' => 'Plazo ordinario de hasta 5 días hábiles'],
                ],
            ],
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_TIPOS_DESPACHO',
                'nombre' => 'CORRESPONDENCIA - TIPOS DE DESPACHO EXTERNO',
                'descripcion' => 'Modalidades de envío físico de correspondencia hacia el exterior',
                'items' => [
                    ['codigo' => 'MENSAJERIA_INTERNA', 'nombre' => 'MENSAJERÍA INTERNA EMAPA', 'descripcion' => 'Distribución física local por personal de correspondencia'],
                    ['codigo' => 'COURIER_POSTAL', 'nombre' => 'COURIER / CORREO POSTAL', 'descripcion' => 'Envío nacional o interdepartamental mediante empresa de courier'],
                    ['codigo' => 'ENTREGA_DIRECTA', 'nombre' => 'ENTREGA DIRECTA EN MANO', 'descripcion' => 'Entrega personal con sello y firma de recepción inmediata'],
                ],
            ],
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_ESTADOS_DESPACHO',
                'nombre' => 'CORRESPONDENCIA - ESTADOS DE DESPACHO EXTERNO',
                'descripcion' => 'Ciclo de vida del envío físico y control de acuse',
                'items' => [
                    ['codigo' => 'PENDIENTE_DESPACHO', 'nombre' => 'PENDIENTE DE DESPACHO', 'descripcion' => 'Documento en ventanilla esperando salida'],
                    ['codigo' => 'EN_CAMINO', 'nombre' => 'EN CAMINO / EN TRÁNSITO', 'descripcion' => 'En poder del mensajero o courier en ruta'],
                    ['codigo' => 'ENTREGADO_CON_ACUSE', 'nombre' => 'ENTREGADO CON ACUSE DE RECIBO', 'descripcion' => 'Entregado a destino con comprobante y sello registrado'],
                    ['codigo' => 'OBSERVADO', 'nombre' => 'OBSERVADO / DIRECCIÓN NO HALLADA', 'descripcion' => 'Inconveniente o imposibilidad de entrega en destino'],
                ],
            ],
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_TIPOS_SOLICITUD',
                'nombre' => 'CORRESPONDENCIA - SOLICITUDES CIUDADANAS WEB',
                'descripcion' => 'Tipología de trámites ingresados por la ventanilla digital web',
                'items' => [
                    ['codigo' => 'TRAMITE_GENERAL', 'nombre' => 'TRÁMITE GENERAL / CONSULTA', 'descripcion' => 'Solicitud o consulta administrativa general'],
                    ['codigo' => 'VENTA_DIRECTA_HARINA', 'nombre' => 'COMPRA DIRECTA DE HARINA', 'descripcion' => 'Solicitud de provisión directa para panificadores'],
                    ['codigo' => 'COMPRA_GRANOS', 'nombre' => 'VENTA Y ACOPIO DE GRANOS', 'descripcion' => 'Propuesta de acopio de trigo, maíz o arroz'],
                    ['codigo' => 'AUDIENCIA_AUTORIDAD', 'nombre' => 'SOLICITUD DE AUDIENCIA', 'descripcion' => 'Petición de reunión o audiencia ejecutiva'],
                ],
            ],
            [
                'tabla' => 'TABLA_CORRESPONDENCIA_ROLES_PARTICIPANTE',
                'nombre' => 'CORRESPONDENCIA - ROLES DE PARTICIPANTE',
                'descripcion' => 'Encabezado y flujo de firmas documentales',
                'items' => [
                    ['codigo' => 'REMITENTE_DE', 'nombre' => 'DE (REMITENTE)', 'descripcion' => 'Funcionario autor y redactor del documento'],
                    ['codigo' => 'DESTINATARIO_A', 'nombre' => 'A (DESTINATARIO PRINCIPAL)', 'descripcion' => 'Autoridad o funcionario a quien se dirige el documento'],
                    ['codigo' => 'VIA', 'nombre' => 'VÍA (CONDUCTO REGULAR)', 'descripcion' => 'Jefe o director intermedio para visto bueno'],
                    ['codigo' => 'CC', 'nombre' => 'CC (COPIA INFORMATIVA)', 'descripcion' => 'Copia para conocimiento y archivo de dependencias'],
                ],
            ],
        ];

        foreach ($grupos as $grupo) {
            // 1. Cabecera Origen (valor 0)
            Parametrica::updateOrCreate(
                [
                    'param_tabla' => $grupo['tabla'],
                    'param_codigo' => 'ORIGEN',
                    'param_valor' => 0,
                ],
                [
                    'param_nombre' => $grupo['nombre'],
                    'param_descripcion' => $grupo['descripcion'],
                    'param_estado' => 'A',
                    'param_usr_registrado' => 1,
                ]
            );

            // 2. Elementos Hijos (valor > 0)
            $orden = 1;
            foreach ($grupo['items'] as $item) {
                Parametrica::updateOrCreate(
                    [
                        'param_tabla' => $grupo['tabla'],
                        'param_codigo' => $item['codigo'],
                    ],
                    [
                        'param_nombre' => $item['nombre'],
                        'param_descripcion' => $item['descripcion'],
                        'param_valor' => $orden++,
                        'param_estado' => 'A',
                        'param_usr_registrado' => 1,
                    ]
                );
            }
        }
    }
}
