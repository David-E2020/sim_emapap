<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoFacturacion extends Model
{
    protected $table = 'comercial.periodos_facturacion';

    public $timestamps = false;

    protected $fillable = [
        'periodo',
        'mes',
        'gestion',
        'fecha_inicio_consumo',
        'fecha_fin_consumo',
        'fecha_vencimiento_pago',
        'estado',
        'observaciones',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'mes' => 'integer',
        'gestion' => 'integer',
        'fecha_inicio_consumo' => 'date',
        'fecha_fin_consumo' => 'date',
        'fecha_vencimiento_pago' => 'date',
    ];

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaMensual::class, 'id_periodo');
    }
}
