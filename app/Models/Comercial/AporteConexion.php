<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;

class AporteConexion extends Model
{
    protected $table = 'comercial.aportes_conexiones';

    protected $fillable = [
        'tipo_servicio',
        'periodo',
        'codigo_socio',
        'nombre_socio',
        'zona',
        'estado',
        'fecha',
        'aporte',
        'instalacion',
        'total',
        'abono',
        'saldo',
        'plazo',
        'pagado',
        'fecha_pago',
        'orden',
        'factura',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_pago' => 'date',
        'aporte' => 'decimal:2',
        'instalacion' => 'decimal:2',
        'total' => 'decimal:2',
        'abono' => 'decimal:2',
        'saldo' => 'decimal:2',
        'plazo' => 'integer',
        'pagado' => 'boolean',
    ];
}
