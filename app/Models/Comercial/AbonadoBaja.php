<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;

class AbonadoBaja extends Model
{
    protected $table = 'comercial.abonados_bajas';

    protected $fillable = [
        'codigo_socio',
        'nombre_socio',
        'ci_ruc',
        'fecha_baja',
        'motivo',
        'factura',
        'importe',
        'saldo',
        'observaciones',
    ];

    protected $casts = [
        'fecha_baja' => 'date',
        'importe' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];
}
