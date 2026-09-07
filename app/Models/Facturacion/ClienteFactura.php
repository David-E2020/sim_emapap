<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClienteFactura extends Model
{
    protected $table = 'facturacion.clientes';

    public $timestamps = false;

    protected $fillable = [
        'codigo_tipo_documento_identidad',
        'numero_documento',
        'complemento',
        'nombre_razon_social',
        'correo_electronico',
        'telefono',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_cliente');
    }
}
