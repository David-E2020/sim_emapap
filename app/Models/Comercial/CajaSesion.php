<?php

declare(strict_types=1);

namespace App\Models\Comercial;

use App\Models\Facturacion\Factura;
use App\Models\Facturacion\SiatPuntoVenta;
use App\Models\Facturacion\SiatSucursal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CajaSesion extends Model
{
    protected $table = 'comercial.caja_sesiones';

    public $timestamps = false;

    protected $fillable = [
        'numero_sesion',
        'id_cajero',
        'id_sucursal',
        'id_punto_venta',
        'fecha_apertura',
        'fecha_cierre',
        'monto_apertura',
        'monto_ventas_efectivo',
        'monto_ventas_qr_banco',
        'monto_ingresos_extra',
        'monto_egresos_extra',
        'monto_esperado_efectivo',
        'monto_cierre_declarado',
        'diferencia',
        'desglose_billetes',
        'estado',
        'observaciones_apertura',
        'observaciones_cierre',
        'id_supervisor_cierre',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'monto_apertura' => 'float',
        'monto_ventas_efectivo' => 'float',
        'monto_ventas_qr_banco' => 'float',
        'monto_ingresos_extra' => 'float',
        'monto_egresos_extra' => 'float',
        'monto_esperado_efectivo' => 'float',
        'monto_cierre_declarado' => 'float',
        'diferencia' => 'float',
        'desglose_billetes' => 'array',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_cajero');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(SiatSucursal::class, 'id_sucursal');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'id_punto_venta');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_supervisor_cierre');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(CajaMovimiento::class, 'id_sesion');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaMensual::class, 'id_sesion_caja');
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(ConvenioCuota::class, 'id_sesion_caja');
    }

    public function recibos(): HasMany
    {
        return $this->hasMany(ReciboCaja::class, 'id_sesion_caja');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_sesion_caja');
    }

    /**
     * Recalcula los totales acumulados de la sesión a partir de los pagos registrados.
     */
    public function recalcularTotales(): void
    {
        $lecturasEfectivo = (float) $this->lecturas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', 1);
            })->sum('total_facturado');

        $lecturasElectronico = (float) $this->lecturas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', '!=', 1);
            })->sum('total_facturado');

        $cuotasEfectivo = (float) $this->cuotas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', 1);
            })->sum('monto_cuota');

        $cuotasElectronico = (float) $this->cuotas()
            ->whereHas('facturaSiat', function ($q) {
                $q->where('codigo_metodo_pago', '!=', 1);
            })->sum('monto_cuota');

        $recibosEfectivo = (float) $this->recibos()->where('estado', 'VALIDO')->sum('monto_total');

        $ingresosExtra = (float) $this->movimientos()->where('tipo', 'INGRESO')->sum('monto');
        $egresosExtra = (float) $this->movimientos()->where('tipo', 'EGRESO')->sum('monto');

        $totalVentasEfectivo = round($lecturasEfectivo + $cuotasEfectivo + $recibosEfectivo, 2);
        $totalVentasElectronico = round($lecturasElectronico + $cuotasElectronico, 2);

        $esperado = round($this->monto_apertura + $totalVentasEfectivo + $ingresosExtra - $egresosExtra, 2);

        $this->update([
            'monto_ventas_efectivo' => $totalVentasEfectivo,
            'monto_ventas_qr_banco' => $totalVentasElectronico,
            'monto_ingresos_extra' => $ingresosExtra,
            'monto_egresos_extra' => $egresosExtra,
            'monto_esperado_efectivo' => $esperado,
        ]);
    }

    public function comprobante(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Contabilidad\Comprobante::class, 'id_referencia_origen')
            ->where('origen_modulo', 'COMERCIAL_CAJA')
            ->where('_estado', 'ACTIVO')
            ->where('estado', '!=', 'ANULADO');
    }

    public function getIdComprobanteAttribute(): ?int
    {
        return $this->comprobante?->id;
    }
}
