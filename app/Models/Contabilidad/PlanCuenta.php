<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanCuenta extends Model
{
    protected $table = 'contabilidad.plan_cuentas';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'id_cuenta_padre',
        'nivel',
        'naturaleza',
        'tipo',
        'permite_movimiento',
        'codigo_mefp',
        'estado',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'nivel' => 'integer',
        'permite_movimiento' => 'boolean',
    ];

    protected $appends = [
        'es_imputable',
        'padre_id',
        'tipo_cuenta',
    ];

    public function getEsImputableAttribute(): bool
    {
        return (bool) $this->permite_movimiento;
    }

    public function setEsImputableAttribute($value): void
    {
        $this->attributes['permite_movimiento'] = (bool) $value;
    }

    public function getPadreIdAttribute(): ?int
    {
        return $this->id_cuenta_padre ? (int) $this->id_cuenta_padre : null;
    }

    public function setPadreIdAttribute($value): void
    {
        $this->attributes['id_cuenta_padre'] = $value ? (int) $value : null;
    }

    public function getTipoCuentaAttribute(): ?string
    {
        return $this->tipo;
    }

    public function setTipoCuentaAttribute($value): void
    {
        $this->attributes['tipo'] = $value;
    }

    public function cuentaPadre(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_cuenta_padre');
    }

    public function subcuentas(): HasMany
    {
        return $this->hasMany(self::class, 'id_cuenta_padre')->orderBy('codigo');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'id_cuenta');
    }

    /**
     * Scope para filtrar cuentas imputables (que permiten registrar movimientos).
     */
    public function scopeImputables($query)
    {
        return $query->where('permite_movimiento', true)->where('_estado', 'ACTIVO');
    }
}
