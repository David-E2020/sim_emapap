<?php

namespace App\ReporteExcel;

use App\Models\Logistica\Granos;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AcopioExport implements FromCollection, WithHeadings
{    public function __construct($campana, $programa, $codigo, $consulta)
    {
        $this->campania_id= $planta_id;
        $this->programa_id = $almacen_id;
        $this->codigo_id = $gestion_id;
        $this->consulta_id = $grano_id;
    }
   public function collection()
   {
        return Granos::all();
   }
   public function headings(): array
    {
        return [
            'ID',
            'Código Solicitud',
            'Tipo Solicitud',
            'Proceso ID',
            'Fecha Solicitud',
            'Número Boleta',
            'Origen ID',
            'Destino ID',
            'Transportadora ID',
            'Contrato ID',
            'Data',
            'Estado Registro',
            'Número Sistema',
            'Fecha Regularización',
            'Estado Regularización',
            'Usuario Regularización',
            'Fecha Autorización',
            'Estado Autorización',
            'Usuario Autorización',
            'Usuario Registrado',
            'Usuario Modificado',
            'Usuario Eliminado',
            'Fecha Creación',
            'Fecha Actualización',
            'Fecha Eliminación',
            'Tipo Salida',
            'Archivo',
            'Fecha Archivo',
            'Usuario Archivo',
            'Observación',
            'Estado Archivo',
        ];
    }
}

