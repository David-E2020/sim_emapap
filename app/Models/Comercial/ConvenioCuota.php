<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use App\Models\Facturacion\Factura;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvenioCuota extends Model
{
    protected $table = 'comercial.convenio_cuotas';

    public $timestamps = false;

    protected $fillable = [
        'id_convenio',
        'numero_cuota',
        'periodo',
        'monto_cuota',
        'fecha_vencimiento',
        'estado_pago',
        'id_factura',
        'fecha_pago',
        'id_cajero',
        'id_sesion_caja',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'numero_cuota' => 'integer',
        'monto_cuota' => 'decimal:2',
        'fecha_vencimiento' => 'date',
        'fecha_pago' => 'datetime',
    ];

    public function convenio(): BelongsTo
    {
        return $this->belongsTo(ConvenioPago::class, 'id_convenio');
    }

    public function facturaSiat(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_cajero');
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'id_sesion_caja');
    }
}
