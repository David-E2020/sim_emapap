<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvenioPago extends Model
{
    protected $table = 'comercial.convenios_pago';

    public $timestamps = false;

    protected $fillable = [
        'id_abonado',
        'numero_convenio',
        'monto_deuda_total',
        'pago_inicial',
        'saldo_financiado',
        'plazo_meses',
        'monto_cuota_mensual',
        'fecha_suscripcion',
        'estado',
        'glosa',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'monto_deuda_total' => 'decimal:2',
        'pago_inicial' => 'decimal:2',
        'saldo_financiado' => 'decimal:2',
        'monto_cuota_mensual' => 'decimal:2',
        'plazo_meses' => 'integer',
        'fecha_suscripcion' => 'date',
    ];

    public function abonado(): BelongsTo
    {
        return $this->belongsTo(Abonado::class, 'id_abonado');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(ConvenioCuota::class, 'id_convenio')->orderBy('numero_cuota');
    }
}
