<?php

declare(strict_types=1);

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiatSucursal extends Model
{
    protected $table = 'facturacion.sucursales';

    public $timestamps = false;

    protected $fillable = [
        'codigo_sucursal',
        'nombre',
        'direccion',
        'telefono',
        'municipio',
        'departamento',
        '_estado',
        '_transaccion',
        '_usuario_creacion',
        '_fecha_creacion',
        '_usuario_modificacion',
        '_fecha_modificacion',
    ];

    public function puntosVenta(): HasMany
    {
        return $this->hasMany(SiatPuntoVenta::class, 'id_sucursal');
    }

    public function cuis(): HasMany
    {
        return $this->hasMany(SiatCuis::class, 'id_sucursal');
    }

    public function cufd(): HasMany
    {
        return $this->hasMany(SiatCufd::class, 'id_sucursal');
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'id_sucursal');
    }
}
