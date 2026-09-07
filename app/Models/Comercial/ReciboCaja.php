<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReciboCaja extends Model
{
    protected $table = 'comercial.recibos_caja';

    public $timestamps = false;

    protected $fillable = [
        'numero_recibo',
        'id_abonado',
        'nombre_cliente',
        'documento_cliente',
        'concepto_tipo',
        'descripcion',
        'monto_total',
        'fecha_cobro',
        'id_cajero',
        'estado',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'fecha_cobro' => 'datetime',
    ];

    public function abonado(): BelongsTo
    {
        return $this->belongsTo(Abonado::class, 'id_abonado');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_cajero');
    }
}
