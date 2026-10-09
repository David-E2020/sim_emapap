<?php

declare(strict_types=1);

namespace Database\Seeders\Rrhh;

use App\Models\Parametrica;
use Illuminate\Database\Seeder;

class RrhhParametricasSeeder extends Seeder
{
    public function run(): void
    {
        $parametricasRrhh = [
            [
                'tabla' => 'TABLA_RRHH_TIPO_CONTRATO',
                'nombre' => 'RRHH - TIPOS DE CONTRATO Y CONDICIÓN',
                'descripcion' => 'Modalidades de vinculación laboral institucional',
                'items' => [
                    ['codigo' => 'PLANTA', 'nombre' => 'Personal de Planta Permanente', 'detalle' => 'Funcionario titular con ítem institucional'],
                    ['codigo' => 'EVENTUAL', 'nombre' => 'Personal Contrato Eventual', 'detalle' => 'Contrato a plazo fijo por partida presupuestaria'],
                    ['codigo' => 'CONSULTOR', 'nombre' => 'Consultoría en Línea / Producto', 'detalle' => 'Servicios especializados por contrato'],
                    ['codigo' => 'PASANTE', 'nombre' => 'Pasantía / Práctica Preprofesional', 'detalle' => 'Convenio interinstitucional o universitario'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_NIVEL_INSTRUCCION',
                'nombre' => 'RRHH - NIVELES DE INSTRUCCIÓN ACADÉMICA',
                'descripcion' => 'Grados académicos para legajo digital',
                'items' => [
                    ['codigo' => 'PRIMARIA', 'nombre' => 'Educación Primaria', 'detalle' => 'Ciclo primario'],
                    ['codigo' => 'SECUNDARIA', 'nombre' => 'Bachiller en Humanidades', 'detalle' => 'Título de bachiller'],
                    ['codigo' => 'TECNICO_MEDIO', 'nombre' => 'Técnico Medio', 'detalle' => 'Formación técnica media'],
                    ['codigo' => 'TECNICO_SUPERIOR', 'nombre' => 'Técnico Superior', 'detalle' => 'Título técnico superior universitario/instituto'],
                    ['codigo' => 'LICENCIATURA', 'nombre' => 'Licenciatura / Grado Universitario', 'detalle' => 'Título en provisión nacional'],
                    ['codigo' => 'DIPLOMADO', 'nombre' => 'Diplomado de Postgrado', 'detalle' => 'Especialidad modular postgrado'],
                    ['codigo' => 'MAESTRIA', 'nombre' => 'Maestría / Magister', 'detalle' => 'Grado académico de maestría'],
                    ['codigo' => 'DOCTORADO', 'nombre' => 'Doctorado / PhD', 'detalle' => 'Máximo grado académico de investigación'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_GENERO',
                'nombre' => 'RRHH - GÉNERO / SEXO',
                'descripcion' => 'Identificación de género para padrón de personal',
                'items' => [
                    ['codigo' => 'M', 'nombre' => 'Masculino', 'detalle' => 'Hombre'],
                    ['codigo' => 'F', 'nombre' => 'Femenino', 'detalle' => 'Mujer'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_ESTADO_CIVIL',
                'nombre' => 'RRHH - ESTADO CIVIL',
                'descripcion' => 'Estado civil legal del funcionario',
                'items' => [
                    ['codigo' => 'SOLTERO', 'nombre' => 'Soltero(a)', 'detalle' => 'Soltero/a'],
                    ['codigo' => 'CASADO', 'nombre' => 'Casado(a)', 'detalle' => 'Casado/a'],
                    ['codigo' => 'DIVORCIADO', 'nombre' => 'Divorciado(a)', 'detalle' => 'Divorciado/a legalmente'],
                    ['codigo' => 'VIUDO', 'nombre' => 'Viudo(a)', 'detalle' => 'Viudo/a'],
                    ['codigo' => 'CONCUBINATO', 'nombre' => 'Unión Libre / Concubinato', 'detalle' => 'Convivencia legalmente registrada'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_EXPEDIDO_DOC',
                'nombre' => 'RRHH - EXPEDICIÓN DE CÉDULA DE IDENTIDAD',
                'descripcion' => 'Lugar de emisión del documento de identidad (Bolivia / Extranjero)',
                'items' => [
                    ['codigo' => 'LP', 'nombre' => 'La Paz (LP)', 'detalle' => 'Departamento de La Paz'],
                    ['codigo' => 'CB', 'nombre' => 'Cochabamba (CB)', 'detalle' => 'Departamento de Cochabamba'],
                    ['codigo' => 'SC', 'nombre' => 'Santa Cruz (SC)', 'detalle' => 'Departamento de Santa Cruz'],
                    ['codigo' => 'OR', 'nombre' => 'Oruro (OR)', 'detalle' => 'Departamento de Oruro'],
                    ['codigo' => 'PT', 'nombre' => 'Potosí (PT)', 'detalle' => 'Departamento de Potosí'],
                    ['codigo' => 'TJ', 'nombre' => 'Tarija (TJ)', 'detalle' => 'Departamento de Tarija'],
                    ['codigo' => 'CH', 'nombre' => 'Chuquisaca (CH)', 'detalle' => 'Departamento de Chuquisaca'],
                    ['codigo' => 'BE', 'nombre' => 'Beni (BE)', 'detalle' => 'Departamento de Beni'],
                    ['codigo' => 'PD', 'nombre' => 'Pando (PD)', 'detalle' => 'Departamento de Pando'],
                    ['codigo' => 'EX', 'nombre' => 'Extranjero (EX)', 'detalle' => 'Documento de identidad extranjero'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_TIPO_JORNADA',
                'nombre' => 'RRHH - TIPOS DE JORNADA LABORAL',
                'descripcion' => 'Modalidades de horario institucional',
                'items' => [
                    ['codigo' => 'CONTINUO', 'nombre' => 'Horario Continuo (8 Horas)', 'detalle' => 'Ingreso matutino y salida vespertina sin corte'],
                    ['codigo' => 'DISCONTINUO', 'nombre' => 'Horario Discontinuo (Con intervalo de almuerzo)', 'detalle' => 'Turnos mañana y tarde con refrigerio'],
                    ['codigo' => 'ESPECIAL', 'nombre' => 'Turnos Rotativos / Jornada Especial', 'detalle' => 'Personal de acopio, silos o plantas industriales'],
                    ['codigo' => 'NOCTURNO', 'nombre' => 'Jornada Nocturna', 'detalle' => 'Turno nocturno de seguridad o custodia'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_MODELO_BIOMETRICO',
                'nombre' => 'RRHH - MODELOS DE RELOJES BIOMÉTRICOS',
                'descripcion' => 'Dispositivos ZKTeco compatibles en red local',
                'items' => [
                    ['codigo' => 'K40', 'nombre' => 'ZKTeco K40 (Huella + Tarjeta RFID)', 'detalle' => 'Reloj básico TCP/IP con batería de respaldo'],
                    ['codigo' => 'IN01-A', 'nombre' => 'ZKTeco IN01-A (Alta Capacidad)', 'detalle' => 'Capacidad hasta 3,000 huellas y 100,000 marcaciones'],
                    ['codigo' => 'MB360', 'nombre' => 'ZKTeco MB360 (Facial + Huella)', 'detalle' => 'Dispositivo híbrido biométrico con verificación rápida'],
                    ['codigo' => 'VF680', 'nombre' => 'ZKTeco VF680 (Reconocimiento Facial)', 'detalle' => 'Terminal facial autónomo con luz infrarroja'],
                    ['codigo' => 'G3', 'nombre' => 'ZKTeco Green Label G3 (SilkID)', 'detalle' => 'Sensor de huella SilkID ultra sensible'],
                    ['codigo' => 'SENSEFACE', 'nombre' => 'ZKTeco SenseFace 2A (Visible Light)', 'detalle' => 'Reconocimiento facial dinámico y pantalla táctil'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_TIPO_DOCUMENTO_LEGAJO',
                'nombre' => 'RRHH - TIPOS DE DOCUMENTO PARA LEGAJO DIGITAL',
                'descripcion' => 'Clasificación de archivos y certificados de personal',
                'items' => [
                    ['codigo' => 'CI', 'nombre' => 'Cédula de Identidad Vigente', 'detalle' => 'Fotocopia simple o legalizada de CI'],
                    ['codigo' => 'CN', 'nombre' => 'Certificado de Nacimiento', 'detalle' => 'Certificado de nacimiento oficial'],
                    ['codigo' => 'TITULO', 'nombre' => 'Título en Provisión Nacional', 'detalle' => 'Diploma académico o título profesional'],
                    ['codigo' => 'CAS', 'nombre' => 'Certificado de Años de Servicio (CAS)', 'detalle' => 'Certificación emitida por entidad pública previa'],
                    ['codigo' => 'MEMO', 'nombre' => 'Memorándum de Designación / Contrato', 'detalle' => 'Documento de asignación de puesto y cargo'],
                    ['codigo' => 'CV', 'nombre' => 'Currículum Vitae Documentado', 'detalle' => 'Hoja de vida con respaldos laborales'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_BONO_ANTIGUEDAD',
                'nombre' => 'RRHH - ESCALA DE BONO DE ANTIGÜEDAD (DS 21060)',
                'descripcion' => 'Porcentajes legales calculados sobre 3 Salarios Mínimos Nacionales',
                'items' => [
                    ['codigo' => 'CAS_2_4', 'nombre' => '2 a 4 años (5%)', 'detalle' => '{"min":2,"max":4,"porcentaje":0.05}'],
                    ['codigo' => 'CAS_5_7', 'nombre' => '5 a 7 años (11%)', 'detalle' => '{"min":5,"max":7,"porcentaje":0.11}'],
                    ['codigo' => 'CAS_8_10', 'nombre' => '8 a 10 años (18%)', 'detalle' => '{"min":8,"max":10,"porcentaje":0.18}'],
                    ['codigo' => 'CAS_11_14', 'nombre' => '11 a 14 años (26%)', 'detalle' => '{"min":11,"max":14,"porcentaje":0.26}'],
                    ['codigo' => 'CAS_15_19', 'nombre' => '15 a 19 años (34%)', 'detalle' => '{"min":15,"max":19,"porcentaje":0.34}'],
                    ['codigo' => 'CAS_20_24', 'nombre' => '20 a 24 años (42%)', 'detalle' => '{"min":20,"max":24,"porcentaje":0.42}'],
                    ['codigo' => 'CAS_25_MAS', 'nombre' => '25 a más años (50%)', 'detalle' => '{"min":25,"max":99,"porcentaje":0.50}'],
                ],
            ],
            [
                'tabla' => 'TABLA_RRHH_CONFIGURACION_SALARIAL',
                'nombre' => 'RRHH - PARÁMETROS SALARIALES Y DE LEY',
                'descripcion' => 'Valores de referencia para planillas y aportes de ley',
                'items' => [
                    ['codigo' => 'SMN_BOLIVIA', 'nombre' => 'Salario Mínimo Nacional (Bs.)', 'detalle' => '3300.00'],
                    ['codigo' => 'TARIFA_REFRIGERIO', 'nombre' => 'Tarifa Diaria de Refrigerio (Bs.)', 'detalle' => '18.00'],
                    ['codigo' => 'APORTE_GESTORA', 'nombre' => 'Aporte Laboral Gestora Pública (%)', 'detalle' => '12.71'],
                ],
            ],
        ];

        foreach ($parametricasRrhh as $grupo) {
            // 1. Cabecera Origen
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

            // 2. Elementos Paramétricos Hijos
            $orden = 1;
            foreach ($grupo['items'] as $item) {
                Parametrica::updateOrCreate(
                    [
                        'param_tabla' => $grupo['tabla'],
                        'param_codigo' => $item['codigo'],
                    ],
                    [
                        'param_nombre' => $item['nombre'],
                        'param_descripcion' => $item['detalle'],
                        'param_valor' => $orden++,
                        'param_estado' => 'A',
                        'param_usr_registrado' => 1,
                    ]
                );
            }
        }
    }
}
