<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Oficial de Asistencia — EMAPAP</title>
    <style>
        @page { margin: 8mm 10mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8px;
            color: #111;
            margin: 0; padding: 0;
            line-height: 1.25;
        }
        /* CABECERA */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .logo-cell { width: 55px; vertical-align: middle; padding-right: 8px; }
        .logo-circle {
            width: 48px; height: 48px; border-radius: 50%;
            background: #1565C0; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 12px;
            font-weight: bold; text-align: center; line-height: 1;
        }
        .company-cell { vertical-align: top; }
        .company-name {
            font-size: 12px; font-weight: bold; text-transform: uppercase;
            color: #0D47A1; margin-bottom: 2px;
        }
        .company-sub { font-size: 7.5px; color: #555; line-height: 1.25; }
        .doc-cell { width: 190px; vertical-align: top; text-align: right; }
        .doc-box {
            border: 2px solid #1565C0; border-radius: 4px;
            padding: 4px 8px; display: inline-block; text-align: center;
        }
        .doc-title { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #1565C0; }
        .doc-sub { font-size: 7.5px; color: #444; }

        .banda-titulo {
            background: #1565C0; color: #fff;
            font-size: 9px; font-weight: bold; text-align: center;
            padding: 3px 0; margin-bottom: 6px; text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* KPIS */
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .kpi-table td {
            text-align: center; padding: 3px 5px;
            border: 1px solid #d0d7de; background: #f8fafc;
        }
        .kpi-lbl { font-size: 7px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-val { font-size: 11.5px; font-weight: bold; color: #0D47A1; }

        /* TABLA PRINCIPAL */
        .data-table { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        .data-table th {
            background: #1565C0; color: #fff; padding: 3.5px 4px;
            text-align: left; font-size: 7.5px; text-transform: uppercase;
            border: 1px solid #0D47A1;
        }
        .data-table td {
            padding: 3px 4px; border: 1px solid #e2e8f0;
        }
        .data-table tr:nth-child(even) td { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .badge {
            font-weight: bold; padding: 1px 4px; border-radius: 4px;
            font-size: 6.8px; display: inline-block;
        }
        .badge-presente { background: #E8F5E9; color: #2E7D32; }
        .badge-atraso { background: #FFF3E0; color: #E65100; }
        .badge-falta { background: #FFEBEE; color: #C62828; }
        .badge-permiso { background: #E3F2FD; color: #1565C0; }

        /* FIRMAS */
        .firmas-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .firmas-table td { text-align: center; vertical-align: bottom; width: 50%; padding: 0 40px; }
        .linea-firma { border-top: 1px solid #333; margin: 0 auto 3px; width: 160px; }
        .firma-nombre { font-size: 7.5px; font-weight: bold; }
        .firma-cargo { font-size: 6.8px; color: #555; }

        .pie-pagina {
            border-top: 1px solid #e2e8f0; margin-top: 10px; padding-top: 3px;
            font-size: 6.8px; color: #64748b; text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <div class="logo-circle">EMA<br>PAP</div>
            </td>
            <td class="company-cell">
                <div class="company-name">EMPRESA MUNICIPAL DE AGUA POTABLE Y ALCANTARILLADO - PATACAMAYA</div>
                <div class="company-sub">
                    DEPARTAMENTO DE RECURSOS HUMANOS · CONTROL DE ASISTENCIA Y BIOMÉTRICO<br>
                    Patacamaya · La Paz · Bolivia · Sistema Integrado EMAPAP
                </div>
            </td>
            <td class="doc-cell">
                <div class="doc-box">
                    <div class="doc-title">REPORTE DE ASISTENCIA</div>
                    <div class="doc-sub">{{ $periodo_subtitulo }}</div>
                    <div style="font-size:7px;color:#555;margin-top:2px;">Emisión: {{ $fecha_emision }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="banda-titulo">
        CONTROL DE ASISTENCIAS Y MARCACIONES BIOMÉTRICAS OFICIALES
    </div>

    <!-- KPIS -->
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-lbl">Total Registros</div>
                <div class="kpi-val">{{ $totales['total_registros'] ?? count($data) }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Presentes</div>
                <div class="kpi-val" style="color:#2E7D32;">{{ $totales['presentes'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Atrasos</div>
                <div class="kpi-val" style="color:#E65100;">{{ $totales['atrasos'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Minutos Atraso</div>
                <div class="kpi-val" style="color:#D84315;">{{ $totales['total_minutos_atraso'] ?? 0 }} m.</div>
            </td>
            <td>
                <div class="kpi-lbl">Faltas</div>
                <div class="kpi-val" style="color:#C62828;">{{ $totales['faltas'] ?? 0 }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Refrigerios Habilitados</div>
                <div class="kpi-val" style="color:#1565C0;">{{ $totales['refrigerios_habilitados'] ?? 0 }} (Bs. {{ number_format($totales['monto_refrigerio_bs'] ?? 0, 2) }})</div>
            </td>
        </tr>
    </table>

    <!-- TABLA DE ASISTENCIAS -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:20px;" class="text-center">#</th>
                <th style="width:55px;">Fecha</th>
                <th>Funcionario</th>
                <th style="width:50px;">C.I.</th>
                <th>Cargo</th>
                <th>Unidad</th>
                <th style="width:35px;" class="text-center">Ent. 1</th>
                <th style="width:35px;" class="text-center">Sal. 1</th>
                <th style="width:35px;" class="text-center">Ent. 2</th>
                <th style="width:35px;" class="text-center">Sal. 2</th>
                <th style="width:35px;" class="text-center">Atraso</th>
                <th style="width:50px;" class="text-center">Estado</th>
                <th style="width:35px;" class="text-center">Refrig.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $idx => $r)
            @php
                $st = strtoupper($r['estado'] ?? 'FALTA');
                $badgeClass = 'badge-falta';
                if ($st === 'PRESENTE') $badgeClass = 'badge-presente';
                elseif ($st === 'ATRASO') $badgeClass = 'badge-atraso';
                elseif ($st === 'PERMISO' || $st === 'COMISION') $badgeClass = 'badge-permiso';
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $r['fecha'] }}</td>
                <td><strong>{{ $r['funcionario'] }}</strong></td>
                <td>{{ $r['ci'] }}</td>
                <td>{{ $r['cargo'] }}</td>
                <td>{{ $r['unidad'] }}</td>
                <td class="text-center">{{ $r['entrada_1'] ?? '-' }}</td>
                <td class="text-center">{{ $r['salida_1'] ?? '-' }}</td>
                <td class="text-center">{{ $r['entrada_2'] ?? '-' }}</td>
                <td class="text-center">{{ $r['salida_2'] ?? '-' }}</td>
                <td class="text-center" style="{{ ($r['minutos_atraso'] ?? 0) > 0 ? 'color:#E65100;font-weight:bold;' : '' }}">
                    {{ ($r['minutos_atraso'] ?? 0) > 0 ? $r['minutos_atraso'] . 'm' : '-' }}
                </td>
                <td class="text-center">
                    <span class="badge {{ $badgeClass }}">{{ $st }}</span>
                </td>
                <td class="text-center" style="font-weight:bold; color: {{ !empty($r['merece_refrigerio']) ? '#2E7D32' : '#9E9E9E' }};">
                    {{ !empty($r['merece_refrigerio']) ? 'SI' : 'NO' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FIRMAS -->
    <table class="firmas-table">
        <tr>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">Control de Personal y Biométrico</div>
                <div class="firma-cargo">Departamento de Recursos Humanos · EMAPAP</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">Jefatura de Recursos Humanos</div>
                <div class="firma-cargo">Visto Bueno y Aprobación Institucional · EMAPAP</div>
            </td>
        </tr>
    </table>

    <div class="pie-pagina">
        Reporte generado automáticamente a partir de los registros de marcación biométrica verificados · Sistema Integrado EMAPAP · {{ $fecha_emision }}
    </div>

</body>
</html>
