<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimientos extends Model {
    use HasFactory;
    use SoftDeletes;
    protected $table      = "public.movimientos";
    protected $primaryKey = 'mv_id';
    protected $casts      = [
        'mv_datos' => 'json',
    ];
    public function movimiento_detalle() {
        return $this->hasMany('App\MovimientoDetalle', 'mvd_mv_id', 'mv_id')->with('articulo');
    }
    //origen y destino se baja por el tipo de movimiento
    // reemplazo id por id_sucursal
    public function destino() {
        return $this->hasOne('App\Sucursal', 'id', 'mv_destino_id');
    }
    //PUEDE SER PROVEEDOR / SUCURSAL / ALMACEN
    public function origen() {
        return $this->hasOne('App\Sucursal', 'id', 'mv_origen_id');
    }
    //lista de acuerdo a movimiento el origen de proveedores de ingreso directo por terceros
    public function origen_proveedor() {
        return $this->hasOne('App\Proveedor', 'id', 'mv_origen_id');
    }
    public function usuario() {
        return $this->hasOne('App\Laravue\Models\User', 'id', 'mv_usr_registrado');
    }

    public function tipo_movimiento() {
        return $this->hasOne('App\Parametricas', 'param_valor', 'mv_tipo_movimiento_id')->where('param_tabla', 'TABLA_TIPO_MOVIMIENTO');
    }

    public function tipo_estado() {
        return $this->hasOne('App\Parametricas', 'param_valor', 'mv_estado_id')->where('param_tabla', 'TABLA_TIPO_ESTADO');
    }
    public function proveedor() {
        return $this->hasOne('App\Proveedor', 'id', 'proveedor_id');
    }
    public function cliente() {
        return $this->hasOne('App\EmapaBilleteraCliente', 'idcliente', 'mv_datos->idcliente')->where('idcliente', 8000026);
    }

}
