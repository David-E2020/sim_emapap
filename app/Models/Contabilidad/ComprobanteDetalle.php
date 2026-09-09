<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComprobanteDetalle extends Model
{
    protected $table = 'contabilidad.comprobante_detalles';

    public $timestamps = false;

    protected $fillable = [
        'id_comprobante',
        'id_cuenta',
        'id_centro_costo',
        'glosa_linea',
        'debe',
        'haber',
        'orden',
        '_estado',
        '_fecha_creacion',
    ];

    protected $casts = [
        'debe' => 'float',
        'haber' => 'float',
        'orden' => 'integer',
    ];

    protected $appends = [
        'plan_cuenta_id',
        'centro_costo_id',
        'glosa',
    ];

    public function getPlanCuentaIdAttribute(): ?int
    {
        return $this->id_cuenta ? (int) $this->id_cuenta : null;
    }

    public function setPlanCuentaIdAttribute($value): void
    {
        $this->attributes['id_cuenta'] = $value ? (int) $value : null;
    }

    public function getCentroCostoIdAttribute(): ?int
    {
        return $this->id_centro_costo ? (int) $this->id_centro_costo : null;
    }

    public function setCentroCostoIdAttribute($value): void
    {
        $this->attributes['id_centro_costo'] = $value ? (int) $value : null;
    }

    public function getGlosaAttribute(): ?string
    {
        return $this->glosa_linea;
    }

    public function setGlosaAttribute($value): void
    {
        $this->attributes['glosa_linea'] = $value;
    }

    public function comprobante(): BelongsTo
    {
        return $this->belongsTo(Comprobante::class, 'id_comprobante');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(PlanCuenta::class, 'id_cuenta');
    }

    public function centroCosto(): BelongsTo
    {
        return $this->belongsTo(CentroCosto::class, 'id_centro_costo');
    }
}
