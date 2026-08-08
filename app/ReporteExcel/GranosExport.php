<?php

namespace App\ReporteExcel;

use App\Models\Logistica\Granos;
use App\Models\Logistica\SolicitudLogistica;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Reader\Xls\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GranosExport implements FromQuery, WithHeadings
{
    // FromCollection
//    public function collection()
//    {
//         return Granos::all();
//    }
use Exportable;
public function query()
{
    return DB::table('logistica.solicitud_logistica as sli')
    ->select(
        'sli.codigo_solicitud',
        'sli.tipo_solicitud',
        'sli.proceso_id',
        'sli.created_at',
        'sli.fecha_solicitud',
        'sli.fecha_solicitud',
        'sli.nro_boleta',
        's1.nombre AS nombre_origen',
        's2.nombre AS nombre_destino',
        'transportadora.nombre AS nombre_transportadora',
        'u.name AS nombre_usuario_autorizacion',
        'u2.name AS nombre_usuario_registrado',
        'v.placa AS placa_vehiculo',
        'c.nombre_completo AS nombre_conductor',
        DB::raw("(sli.data->>'tipo_solicitud') AS tipo_solicitud"),
        DB::raw("(sli.data->>'responsable_entrega_nombre') AS responsable_entrega_nombre"),
        DB::raw("(sli.data->>'responsable_recepcion_nombre') AS responsable_recepcion_nombre"),
        'u4.name AS nombre_responsable_recepcion'
    )
    ->join('public.distribuidoras AS transportadora', 'sli.transportadora_id', '=', 'transportadora.id')
    ->join('public.sucursals as s1', 'sli.origen_id', '=', 's1.id')
    ->join('public.sucursals as s2', 'sli.destino_id', '=', 's2.id')
    ->join('public.users as u', 'sli.usr_autorizacion', '=', 'u.id')
    ->join('public.users as u2', 'sli.usr_registrado', '=', 'u2.id')
    ->leftJoin('public.conductors as c', DB::raw("(sli.data->>'conductor')::int"), '=', 'c.id')
    ->leftJoin('public.transportes as v', DB::raw("(sli.data->>'vehiculo')::int"), '=', 'v.id')
    ->leftJoin('public.users as u3', DB::raw("(sli.data->>'responsable_entrega_user_id')::int"), '=', 'u3.id')
    ->leftJoin('public.users as u4', DB::raw("(sli.data->>'responsable_recepcion_user_id')::int"), '=', 'u4.id')
    ->orderBy('sli.created_at', 'DESC');
}
   public function headings(): array
    {
        return [
            'Código Solicitud',
            'Tipo Solicitud',
            'Proceso ID',
            'Fecha Creación',
            'Fecha Solicitud',
            'Número Boleta',
            'Nombre Origen',
            'Nombre Destino',
            'Nombre Transportadora',
            'Nombre Usuario Autorización',
            'Nombre Usuario Registrado',
            'Placa Vehículo',
            'Nombre Conductor',
            'Tipo Solicitud',
            'Responsable Entrega Nombre (Data)',
            'Responsable Recepción Nombre (Data)',
            'Nombre Responsable Recepción'
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        $sheet->getStyle('A1:Q1')->getAlignment()->setHorizontal('center');

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $sheet->getStyle('A1:Q1')->applyFromArray($borderStyle);

        return $sheet;
    }
}

