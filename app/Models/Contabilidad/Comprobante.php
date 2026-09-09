<?php

declare(strict_types=1);

namespace App\Models\Contabilidad;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comprobante extends Model
{
    protected $table = 'contabilidad.comprobantes';

    public $timestamps = false;

    protected $fillable = [
        'numero_comprobante',
        'tipo',
        'fecha',
        'id_gestion',
        'mes',
        'glosa_principal',
        'beneficiario',
        'documento_beneficiario',
        'tipo_documento_respaldo',
        'numero_documento_respaldo',
        'total_debe',
        'total_haber',
        'diferencia',
        'estado',
        'origen_modulo',
        'id_referencia_origen',
        'id_usuario_elaboracion',
        'id_usuario_aprobacion',
        'fecha_aprobacion',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'total_debe' => 'float',
        'total_haber' => 'float',
        'diferencia' => 'float',
        'fecha_aprobacion' => 'datetime',
        'mes' => 'integer',
    ];

    protected $appends = [
        'nro_comprobante',
        'glosa',
    ];

    public function getNroComprobanteAttribute(): ?string
    {
        return $this->numero_comprobante;
    }

    public function setNroComprobanteAttribute($value): void
    {
        $this->attributes['numero_comprobante'] = $value;
    }

    public function getGlosaAttribute(): ?string
    {
        return $this->glosa_principal;
    }

    public function setGlosaAttribute($value): void
    {
        $this->attributes['glosa_principal'] = $value;
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionContable::class, 'id_gestion');
    }

    public function usuarioElaboracion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_elaboracion');
    }

    public function usuarioAprobacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_aprobacion');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(ComprobanteDetalle::class, 'id_comprobante')->orderBy('orden')->orderBy('id');
    }

    /**
     * Recalcula totales de Debe, Haber y Diferencia a partir de los detalles.
     */
    public function recalcularTotales(): void
    {
        $debe = (float) $this->detalles()->sum('debe');
        $haber = (float) $this->detalles()->sum('haber');
        $diff = round(abs($debe - $haber), 2);

        $this->total_debe = round($debe, 2);
        $this->total_haber = round($haber, 2);
        $this->diferencia = $diff;

        $this->save();
    }

    /**
     * Valida estricta partida doble con tolerancia máxima de 0.00 Bs.
     */
    public function estaBalanceado(): bool
    {
        return round(abs((float) $this->total_debe - (float) $this->total_haber), 2) === 0.00;
    }
}
