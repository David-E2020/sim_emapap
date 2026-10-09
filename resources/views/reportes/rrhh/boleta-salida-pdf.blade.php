<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta Oficial de Salida — {{ $funcionario->nombres ?? 'Funcionario' }} {{ $funcionario->primer_apellido ?? '' }} — {{ $solicitud->cite }}</title>
    <style>
        @page { margin: 12mm 15mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #111;
            margin: 0; padding: 0;
            line-height: 1.4;
        }
        /* CABECERA */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .logo-cell { width: 65px; vertical-align: middle; padding-right: 10px; }
        .logo-circle {
            width: 55px; height: 55px; border-radius: 50%;
            background: #1565C0; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 14px;
            font-weight: bold; text-align: center; line-height: 1;
        }
        .company-cell { vertical-align: top; }
        .company-name {
            font-size: 14px; font-weight: bold; text-transform: uppercase;
            color: #0D47A1; margin-bottom: 2px;
        }
        .company-sub { font-size: 8.5px; color: #555; line-height: 1.3; }
        .doc-cell { width: 180px; vertical-align: top; text-align: right; }
        .doc-box {
            border: 2px solid #1565C0; border-radius: 5px;
            padding: 6px 10px; display: inline-block; text-align: center;
        }
        .doc-title { font-size: 8.5px; font-weight: bold; text-transform: uppercase; color: #1565C0; }
        .doc-cite { font-size: 12px; font-weight: bold; color: #0D47A1; margin: 2px 0; }
        .doc-fecha { font-size: 8px; color: #444; }

        .banda-titulo {
            background: #1565C0; color: #fff;
            font-size: 10px; font-weight: bold; text-align: center;
            padding: 4px 0; margin-bottom: 10px; text-transform: uppercase;
            letter-spacing: 0.8px; border-radius: 3px;
        }

        .tabla-seccion {
            width: 100%; border-collapse: collapse; margin-bottom: 10px;
            border: 1px solid #d0d7de; font-size: 9px;
        }
        .tabla-seccion td {
            padding: 5px 8px; border: 1px solid #e1e4e8; vertical-align: middle;
        }
        .lbl {
            font-weight: bold; background: #f0f4f8; width: 130px; color: #1565C0;
        }

        .seccion-header {
            background: #e8f0fe; font-weight: bold; color: #0D47A1;
            font-size: 9.5px; text-transform: uppercase; padding: 4px 8px;
            border-left: 3px solid #1565C0; margin-bottom: 4px;
        }

        .badge-estado {
            display: inline-block; padding: 3px 10px; border-radius: 12px;
            font-size: 8.5px; font-weight: bold; text-transform: uppercase;
        }
        .estado-aprobado { background: #E8F5E9; color: #2E7D32; border: 1px solid #4CAF50; }
        .estado-pendiente { background: #FFF8E1; color: #F57F17; border: 1px solid #FFB300; }
        .estado-rechazado { background: #FFEBEE; color: #C62828; border: 1px solid #E53935; }

        .motivo-box {
            background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 4px;
            padding: 8px 12px; font-size: 9.5px; color: #1e293b; margin-top: 4px;
            line-height: 1.5; min-height: 45px;
        }

        /* FIRMAS */
        .firmas-table { width: 100%; border-collapse: collapse; margin-top: 30px; }
        .firmas-table td { text-align: center; vertical-align: bottom; width: 33.33%; padding: 0 10px; }
        .linea-firma { border-top: 1px solid #333; margin: 0 auto 4px; width: 140px; }
        .firma-nombre { font-size: 8.5px; font-weight: bold; color: #111; }
        .firma-cargo { font-size: 7.5px; color: #555; line-height: 1.2; }

        .pie-pagina {
            border-top: 1px solid #e2e8f0; margin-top: 20px; padding-top: 6px;
            font-size: 7.5px; color: #64748b; text-align: center;
        }
    </style>
</head>
<body>

    <!-- CABECERA INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <div class="logo-circle">EMA<br>PAP</div>
            </td>
            <td class="company-cell">
                <div class="company-name">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - PATACAMAYA</div>
                <div class="company-sub">
                    DEPARTAMENTO DE RECURSOS HUMANOS · GESTIÓN DE PERSONAL<br>
                    Patacamaya · La Paz · Bolivia · Plaza Bolívar Zona Estación<br>
                    Sistema Integrado de Gestión Administrativa
                </div>
            </td>
            <td class="doc-cell">
                <div class="doc-box">
                    <div class="doc-title">BOLETA OFICIAL</div>
                    <div class="doc-cite">{{ $solicitud->cite }}</div>
                    <div class="doc-fecha">Emisión: {{ $fecha_emision ?? now()->format('d/m/Y H:i') }}</div>
                    <div style="margin-top: 3px;">
                        @php
                            $estado = strtoupper($funcionario->estado_aprobacion ?? 'PENDIENTE');
                        @endphp
                        @if($estado === 'APROBADO')
                            <span class="badge-estado estado-aprobado">✔ APROBADO</span>
                        @elseif($estado === 'RECHAZADO')
                            <span class="badge-estado estado-rechazado">✖ RECHAZADO</span>
                        @else
                            <span class="badge-estado estado-pendiente">⏳ EN TRÁMITE</span>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="banda-titulo">
        BOLETA DE AUTORIZACIÓN OFICIAL — {{ $solicitud->permiso->nombre ?? 'PERMISO PARTICULAR' }}
    </div>

    <!-- 1. DATOS DEL FUNCIONARIO -->
    <div class="seccion-header">1. Información del Funcionario Solicitante</div>
    <table class="tabla-seccion">
        <tr>
            <td class="lbl">Funcionario:</td>
            <td colspan="3">
                <strong>{{ $funcionario->nombres ?? '' }} {{ $funcionario->primer_apellido ?? '' }} {{ $funcionario->segundo_apellido ?? '' }}</strong>
            </td>
            <td class="lbl">Nº Documento (C.I.):</td>
            <td><strong>{{ $funcionario->nro_documento ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Cargo Oficial:</td>
            <td colspan="3">{{ $funcionario->cargo ?? 'Personal de Planta' }}</td>
            <td class="lbl">Unidad / Área:</td>
            <td>{{ $funcionario->unidad ?? 'Operaciones' }}</td>
        </tr>
    </table>

    <!-- 2. DETALLE DEL PERMISO / COMISIÓN / SALIDA -->
    <div class="seccion-header">2. Detalle y Parámetros del Trámite</div>
    <table class="tabla-seccion">
        <tr>
            <td class="lbl">Tipo de Permiso:</td>
            <td>
                <strong>{{ $solicitud->permiso->nombre ?? 'Permiso de Salida' }}</strong>
                ({{ $solicitud->permiso->sigla ?? 'P.P.' }})
            </td>
            <td class="lbl">Tipo de Acción:</td>
            <td><strong>{{ $solicitud->tipo_accion ?? 'SALIDA_PARTICULAR' }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Fecha Inicio:</td>
            <td><strong>{{ $solicitud->fecha_inicio ? \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y') : '-' }}</strong></td>
            <td class="lbl">Fecha Conclusión:</td>
            <td><strong>{{ $solicitud->fecha_fin ? \Carbon\Carbon::parse($solicitud->fecha_fin)->format('d/m/Y') : '-' }}</strong></td>
        </tr>
        <tr>
            <td class="lbl">Horario Solicitado:</td>
            <td>
                @if($solicitud->dia_completo)
                    <span style="font-weight:bold;color:#1565C0;">Jornada Completa</span>
                @else
                    De <strong>{{ $solicitud->hora_inicio ?? '--:--' }}</strong> a <strong>{{ $solicitud->hora_fin ?? '--:--' }}</strong>
                @endif
            </td>
            <td class="lbl">Tiempo Computado:</td>
            <td>
                <strong>{{ $solicitud->horas_solicitadas > 0 ? $solicitud->horas_solicitadas . ' Horas' : ($solicitud->dia_completo ? '1 Día' : '-') }}</strong>
            </td>
        </tr>
        @if($solicitud->lugar)
        <tr>
            <td class="lbl">Lugar de Comisión:</td>
            <td colspan="3"><strong>{{ $solicitud->lugar }}</strong></td>
        </tr>
        @endif
        @if($solicitud->turno_periodo || $solicitud->hora_marcado_omision)
        <tr>
            <td class="lbl">Turno / Marcado:</td>
            <td colspan="3">
                Periodo: <strong>{{ $solicitud->turno_periodo ?? 'N/A' }}</strong> &nbsp;|&nbsp;
                Hora Omitida: <strong>{{ $solicitud->hora_marcado_omision ?? 'N/A' }}</strong>
            </td>
        </tr>
        @endif
    </table>

    <!-- 3. MOTIVO / JUSTIFICACIÓN -->
    <div class="seccion-header">3. Motivo y Justificación Declarada</div>
    <div class="motivo-box">
        {{ $solicitud->motivo ?? 'Sin motivo especificado.' }}
    </div>

    <!-- 4. FIRMAS Y CONFORMIDADES -->
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">{{ $funcionario->nombres ?? '' }} {{ $funcionario->primer_apellido ?? '' }}</div>
                <div class="firma-cargo">Funcionario Solicitante<br>C.I. {{ $funcionario->nro_documento ?? '' }}</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">Jefe Inmediato Superior / RRHH</div>
                <div class="firma-cargo">Vº Bº Autorización<br>EMAPAP Patacamaya</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">Gerencia General</div>
                <div class="firma-cargo">Vº Bº Aprobación Final<br>EMAPAP Patacamaya</div>
            </td>
        </tr>
    </table>

    <!-- PIE -->
    <div class="pie-pagina">
        Documento oficial emitido por el Sistema Integrado EMAPAP · Patacamaya ·
        Verificado electrónicamente bajo el CITE <strong>{{ $solicitud->cite }}</strong> ·
        Generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>
