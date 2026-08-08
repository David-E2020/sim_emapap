<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockExistencia extends Model {
    use HasFactory;
    use SoftDeletes;

    protected $table      = "public.stock_existencias";
    protected $primaryKey = 'sk_id';

    public function articulo() {
        return $this->hasOne('App\Articulos', 'id', 'sk_articulos_id')->with('unidad_medida', 'codigo_impuestos', 'codigo_unidad_medida', 'producto_precios')->select('id', 'nombre', 'codigo', 'estado', 'unidad_medida_id', 'actividad_economica', 'catalogo_sin_id', 'unidad_sin_id');
    }
    public function destino() {
        return $this->hasOne('App\Sucursal', 'id', 'sk_destino_id');
    }
    public function producto() {
        return $this->hasOne('App\Articulos', 'id', 'sk_articulos_id')->with('codigo_impuesto', 'codigo_unidad_medidas')->select('id', 'nombre', 'codigo', 'catalogo_sin_id', 'unidad_sin_id');
    }
}
