<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use App\Models\Comercial\Abonado;
use App\Models\Comercial\CajaSesion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransaccionQr extends Model
{
    protected $table = 'facturacion.transacciones_qr';

    public const ESTADO_PENDING = 'PENDING';
    public const ESTADO_COMPLETED = 'COMPLETED';
    public const ESTADO_EXPIRED = 'EXPIRED';
    public const ESTADO_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'uuid',
        'id_factura',
        'id_abonado',
        'id_caja_sesion',
        'id_usuario',
        'monto',
        'moneda',
        'glosa',
        'qr_payload',
        'qr_imagen_base64',
        'banco_destino',
        'cuenta_destino',
        'estado',
        'transaccion_banco_id',
        'expira_at',
        'pagado_at',
        'metadata',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'expira_at' => 'datetime',
        'pagado_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'id_factura');
    }

    public function abonado(): BelongsTo
    {
        return $this->belongsTo(Abonado::class, 'id_abonado');
    }

    public function cajaSesion(): BelongsTo
    {
        return $this->belongsTo(CajaSesion::class, 'id_caja_sesion');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function estaVencido(): bool
    {
        return $this->estado === self::ESTADO_PENDING && now()->greaterThan($this->expira_at);
    }
}
