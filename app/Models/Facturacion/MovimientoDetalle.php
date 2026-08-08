<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoDetalle extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "public.movimiento_detalles";
    protected $primaryKey = 'mvd_id';
    //protected $foreignKey = 'mvd_mv_id';

    public function articulo() {
        return $this->hasOne('App\Articulos', 'id', 'mvd_articulo_id')->with('categoria','unidad_medida','proveedor');
    }
    public function movimiento() {
        return $this->hasOne('App\Movimientos', 'mv_id', 'mvd_mv_id')->with('destino','origen','tipo_movimiento', 'usuario');
    }
    public function stock_existencia() {
        return $this->hasOne('App\StockExistencia', 'sk_articulos_id', 'mvd_articulo_id');
    }
}
