<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Factura extends Model
{
    protected $table = 'facturacion.facturas';

    public $timestamps = false;

    protected $fillable = [
        'id_sucursal',
        'id_punto_venta',
        'id_sesion_caja',
        'id_cliente',
        'id_abonado',
        'id_cufd',
        'id_evento_significativo',
        'numero_factura',
        'cuf',
        'cufd',
        'codigo_control',
        'fecha_emision',
        'codigo_modalidad',
        'tipo_emision',
        'tipo_factura_documento',
        'codigo_documento_sector',
        'mes',
        'gestion',
        'ciudad',
        'zona',
        'numero_medidor',
        'domicilio_cliente',
        'consumo_periodo',
        'beneficiario_ley_1886',
        'monto_descuento_ley_1886',
        'monto_descuento_tarifa_dignidad',
        'tasa_aseo',
        'tasa_alumbrado',
        'ajuste_no_sujeto_iva',
        'detalle_ajuste_no_sujeto_iva',
        'ajuste_sujeto_iva',
        'detalle_ajuste_sujeto_iva',
        'otros_pagos_no_sujeto_iva',
        'detalle_otros_pagos_no_sujeto_iva',
        'otras_tasas',
        'codigo_autorizacion_sfv',
        'nombre_razon_social',
        'numero_documento',
        'complemento',
        'codigo_tipo_documento_identidad',
        'codigo_metodo_pago',
        'numero_tarjeta',
        'monto_total',
        'monto_total_sujeto_iva',
        'monto_descuento',
        'monto_gift_card',
        'codigo_moneda',
        'tipo_cambio',
        'leyenda',
        'usuario_emision',
        'estado_factura',
        'codigo_recepcion',
        'codigo_motivo_anulacion',
        'fecha_anulacion',
        'es_anulacion_administrativa',
        'nro_resolucion_administrativa',
        'fecha_resolucion_administrativa',
        'tiempo_respuesta_ms',
        'reversion_anulacion_fecha',
        'reversion_anulacion_usuario',
        'xml_firmado_path',
        'pdf_path',
        'representacion_grafica_qr',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    // Constantes de la máquina de 5 estados (homologados con CUCU y SIAT)
    public const ESTADO_PENDING = 'PENDIENTE';
    public const ESTADO_VALIDATED = 'VALIDADA';
    public const ESTADO_CONTINGENCY = 'CONTINGENCIA';
    public const ESTADO_REJECTED = 'RECHAZADA';
    public const ESTADO_CANCELLED = 'ANULADA';

    protected $casts = [
        'fecha_emision' => 'datetime',
        'fecha_anulacion' => 'datetime',
        'fecha_resolucion_administrativa' => 'date',
        'reversion_anulacion_fecha' => 'datetime',
        'es_anulacion_administrativa' => 'boolean',
        'tiempo_respuesta_ms' => 'integer',
        'monto_total' => 'decimal:2',
        'monto_total_sujeto_iva' => 'decimal:2',
        'monto_descuento' => 'decimal:2',
        'monto_gift_card' => 'decimal:2',
        'tipo_cambio' => 'decimal:2',
        'consumo_periodo' => 'decimal:2',
        'beneficiario_ley_1886' => 'boolean',
        'monto_descuento_ley_1886' => 'decimal:2',
        'monto_descuento_tarifa_dignidad' => 'decimal:2',
        'tasa_aseo' => 'decimal:2',
        'tasa_alumbrado' => 'decimal:2',
        'ajuste_no_sujeto_iva' => 'decimal:2',
        'ajuste_sujeto_iva' => 'decimal:2',
        'otros_pagos_no_sujeto_iva' => 'decimal:2',
        'otras_tasas' => 'decimal:2',
    ];

    /**
     * Asegura la serialización de fechas en hora local sin desfase UTC.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function transaccionesQr(): HasMany
    {
        return $this->hasMany(TransaccionQr::class, 'id_factura');
    }

    public function esEstadoFinal(): bool
    {
        return in_array($this->estado_factura, [self::ESTADO_VALIDATED, self::ESTADO_CANCELLED, self::ESTADO_REJECTED, 'VALIDADA', 'ANULADA', 'RECHAZADA'], true);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(SiatSucursal::class, 'id_sucursal');
    }

    public function puntoVenta(): BelongsTo
    {
        return $this->belongsTo(SiatPuntoVenta::class, 'id_punto_venta');
    }

    public function sesionCaja(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Comercial\CajaSesion::class, 'id_sesion_caja');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(ClienteFactura::class, 'id_cliente');
    }

    public function abonado(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Comercial\Abonado::class, 'id_abonado');
    }

    public function cufdRelacion(): BelongsTo
    {
        return $this->belongsTo(SiatCufd::class, 'id_cufd');
    }

    public function cufdModel(): BelongsTo
    {
        return $this->belongsTo(SiatCufd::class, 'id_cufd');
    }

    public function eventoSignificativo(): BelongsTo
    {
        return $this->belongsTo(EventoSignificativo::class, 'id_evento_significativo');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'id_factura');
    }

    public function lecturas(): HasMany
    {
        return $this->hasMany(\App\Models\Comercial\LecturaMensual::class, 'id_factura');
    }
}
