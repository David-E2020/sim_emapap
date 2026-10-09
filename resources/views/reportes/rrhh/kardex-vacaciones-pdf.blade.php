<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex Oficial de Vacaciones — EMAPAP</title>
    <style>
        @page { margin: 10mm 12mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #111;
            margin: 0; padding: 0;
            line-height: 1.3;
        }
        /* CABECERA */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .logo-cell { width: 60px; vertical-align: middle; padding-right: 8px; }
        .logo-circle {
            width: 50px; height: 50px; border-radius: 50%;
            background: #1565C0; display: flex; align-items: center;
            justify-content: center; color: #fff; font-size: 13px;
            font-weight: bold; text-align: center; line-height: 1;
        }
        .company-cell { vertical-align: top; }
        .company-name {
            font-size: 13px; font-weight: bold; text-transform: uppercase;
            color: #0D47A1; margin-bottom: 2px;
        }
        .company-sub { font-size: 8px; color: #555; line-height: 1.3; }
        .doc-cell { width: 180px; vertical-align: top; text-align: right; }
        .doc-box {
            border: 2px solid #1565C0; border-radius: 4px;
            padding: 5px 8px; display: inline-block; text-align: center;
        }
        .doc-title { font-size: 8px; font-weight: bold; text-transform: uppercase; color: #1565C0; }
        .doc-sub { font-size: 8px; color: #444; }

        .banda-titulo {
            background: #1565C0; color: #fff;
            font-size: 9.5px; font-weight: bold; text-align: center;
            padding: 3px 0; margin-bottom: 8px; text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        /* KPIS */
        .kpi-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .kpi-table td {
            width: 25%; text-align: center; padding: 4px 6px;
            border: 1px solid #d0d7de; background: #f8fafc;
        }
        .kpi-lbl { font-size: 7.5px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-val { font-size: 13px; font-weight: bold; color: #0D47A1; }

        /* TABLA PRINCIPAL */
        .data-table { width: 100%; border-collapse: collapse; font-size: 8px; }
        .data-table th {
            background: #1565C0; color: #fff; padding: 4px 5px;
            text-align: left; font-size: 8px; text-transform: uppercase;
            border: 1px solid #0D47A1;
        }
        .data-table td {
            padding: 3.5px 5px; border: 1px solid #e2e8f0;
        }
        .data-table tr:nth-child(even) td { background: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .badge-saldo {
            background: #E8F5E9; color: #2E7D32; font-weight: bold;
            padding: 1px 5px; border-radius: 8px; border: 1px solid #A5D6A7;
            display: inline-block;
        }

        /* FIRMAS */
        .firmas-table { width: 100%; border-collapse: collapse; margin-top: 25px; }
        .firmas-table td { text-align: center; vertical-align: bottom; width: 50%; padding: 0 30px; }
        .linea-firma { border-top: 1px solid #333; margin: 0 auto 3px; width: 160px; }
        .firma-nombre { font-size: 8px; font-weight: bold; }
        .firma-cargo { font-size: 7px; color: #555; }

        .pie-pagina {
            border-top: 1px solid #e2e8f0; margin-top: 15px; padding-top: 4px;
            font-size: 7px; color: #64748b; text-align: center;
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
                    DEPARTAMENTO DE RECURSOS HUMANOS · PLANILLAS Y CONTROL LABORAL<br>
                    Patacamaya · La Paz · Bolivia · Sistema Integrado EMAPAP
                </div>
            </td>
            <td class="doc-cell">
                <div class="doc-box">
                    <div class="doc-title">KARDEX DE VACACIONES</div>
                    <div class="doc-sub">Ley General del Trabajo</div>
                    <div style="font-size:7.5px;color:#555;margin-top:2px;">Emisión: {{ $fecha_emision }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="banda-titulo">
        KARDEX INSTITUCIONAL DE DERECHO VACACIONAL, DIAS UTILIZADOS Y SALDOS
    </div>

    <!-- KPIS -->
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-lbl">Personal Evaluado</div>
                <div class="kpi-val">{{ count($data) }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Total Días Derecho</div>
                <div class="kpi-val" style="color:#2E7D32;">{{ $total_derecho }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Total Días Gozados</div>
                <div class="kpi-val" style="color:#F57F17;">{{ $total_gozados }}</div>
            </td>
            <td>
                <div class="kpi-lbl">Saldo Disponible Global</div>
                <div class="kpi-val" style="color:#1565C0;">{{ $total_saldo }}</div>
            </td>
        </tr>
    </table>

    <!-- LISTA DE VACACIONES -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:25px;" class="text-center">#</th>
                <th>Funcionario</th>
                <th style="width:65px;">C.I.</th>
                <th>Cargo</th>
                <th style="width:65px;" class="text-center">Ingreso</th>
                <th style="width:55px;" class="text-center">Antigüedad</th>
                <th style="width:80px;" class="text-center">Escala LGT</th>
                <th style="width:50px;" class="text-center">Derecho</th>
                <th style="width:50px;" class="text-center">Gozados</th>
                <th style="width:55px;" class="text-center">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $idx => $r)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $r['funcionario'] }}</strong></td>
                <td>{{ $r['ci'] }}</td>
                <td>{{ $r['cargo'] }}</td>
                <td class="text-center">{{ $r['fecha_ingreso'] ?? '-' }}</td>
                <td class="text-center">{{ $r['antiguedad_anios'] }} años</td>
                <td class="text-center">{{ $r['escala_texto'] }}</td>
                <td class="text-center font-weight-bold">{{ $r['dias_derecho'] }} d.</td>
                <td class="text-center">{{ $r['dias_gozados'] }} d.</td>
                <td class="text-center">
                    <span class="badge-saldo">{{ $r['saldo'] }} días</span>
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
                <div class="firma-nombre">Jefatura de Recursos Humanos</div>
                <div class="firma-cargo">Responsable de Planillas y Personal · EMAPAP</div>
            </td>
            <td>
                <div class="linea-firma"></div>
                <div class="firma-nombre">Gerencia General</div>
                <div class="firma-cargo">Aprobación Institucional · EMAPAP</div>
            </td>
        </tr>
    </table>

    <div class="pie-pagina">
        Documento oficial emitido conforme a la Ley General del Trabajo y Decretos Reglamentarios vigentes en el Estado Plurinacional de Bolivia · Sistema Integrado EMAPAP · {{ $fecha_emision }}
    </div>

</body>
</html>
