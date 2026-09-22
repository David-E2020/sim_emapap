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

    public const ESTADO_LECTURA = 'LECTURA';
    public const ESTADO_FACTURACION = 'FACTURACION';
    public const ESTADO_CERRADO = 'CERRADO';

    // Aliases históricos compatibles con FoxPro y versiones previas
    public const ESTADO_ABIERTO = 'ABIERTO';
    public const ESTADO_FACTURADO = 'FACTURADO';

    public function lecturas(): HasMany
    {
        return $this->hasMany(LecturaMensual::class, 'id_periodo');
    }

    /**
     * Retorna la etiqueta legible del estado (Lectura, Facturación, Cerrado).
     */
    public function getEstadoLabelAttribute(): string
    {
        return match (strtoupper((string) $this->estado)) {
            'L', 'LECTURA', 'ABIERTO' => 'Lectura',
            'F', 'FACTURACION', 'FACTURADO' => 'Facturación',
            'C', 'CERRADO' => 'Cerrado',
            default => (string) $this->estado,
        };
    }

    /**
     * Retorna el código de estado unificado (LECTURA, FACTURACION, CERRADO).
     */
    public function getEstadoNormalizadoAttribute(): string
    {
        return match (strtoupper((string) $this->estado)) {
            'L', 'LECTURA', 'ABIERTO' => self::ESTADO_LECTURA,
            'F', 'FACTURACION', 'FACTURADO' => self::ESTADO_FACTURACION,
            'C', 'CERRADO' => self::ESTADO_CERRADO,
            default => (string) $this->estado,
        };
    }

    public function esLectura(): bool
    {
        return in_array(strtoupper((string) $this->estado), ['L', 'LECTURA', 'ABIERTO'], true);
    }

    public function esFacturacion(): bool
    {
        return in_array(strtoupper((string) $this->estado), ['F', 'FACTURACION', 'FACTURADO'], true);
    }

    public function esCerrado(): bool
    {
        return in_array(strtoupper((string) $this->estado), ['C', 'CERRADO'], true);
    }
}
