<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use App\Models\Facturacion\Factura;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturaMensual extends Model
{
    protected $table = 'comercial.lecturas_mensuales';

    public $timestamps = false;

    protected $fillable = [
        'id_periodo',
        'id_abonado',
        'id_medidor',
        'lectura_anterior',
        'lectura_actual',
        'consumo_m3',
        'es_estimada',
        'observacion_lectura',
        'fecha_lectura',
        'id_lecturador',
        'monto_agua',
        'monto_alcantarillado',
        'monto_descuento_ley1886',
        'monto_otros',
        'total_facturado',
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
        'lectura_anterior' => 'decimal:2',
        'lectura_actual' => 'decimal:2',
        'consumo_m3' => 'decimal:2',
        'es_estimada' => 'boolean',
        'monto_agua' => 'decimal:2',
        'monto_alcantarillado' => 'decimal:2',
        'monto_descuento_ley1886' => 'decimal:2',
        'monto_otros' => 'decimal:2',
        'total_facturado' => 'decimal:2',
        'fecha_lectura' => 'datetime',
        'fecha_pago' => 'datetime',
    ];

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoFacturacion::class, 'id_periodo');
    }

    public function abonado(): BelongsTo
    {
        return $this->belongsTo(Abonado::class, 'id_abonado');
    }

    public function medidor(): BelongsTo
    {
        return $this->belongsTo(Medidor::class, 'id_medidor');
    }

    public function facturaSiat(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Facturacion\Factura::class, 'id_factura');
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'id_sesion_caja');
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_cajero');
    }
}
