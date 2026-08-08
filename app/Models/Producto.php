<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model {
	use HasFactory;
	protected $table = 'acopio.productos';
	protected $primaryKey = 'prod_id';
	public $timestamps = true;

	protected $fillable = [
		'prod_codigo',
		'prod_desc',
		'param_tipo_id',
		'param_calidad_id',
		'sin_producto_id',
		'prod_siat_param_unidadmedida_id',
		'prod_presentacion',
		'prod_formato_presentacion',
	];

	public function existencias() {
		return $this->hasMany(Existencia::class, 'sk_id_producto');
	}

	public function productoSiat() {
		return $this->belongsTo(SinProducto::class, 'sin_producto_id')->with(['nandinaProducto']);
	}

	public function movimientoDetalles() {
		return $this->hasMany(MovimientoDetalle::class, 'mvd_id_prod', 'prod_id');
	}

	public function unidadMedida() {
		return $this->belongsTo(Parametrica::class, 'prod_siat_param_unidadmedida_id');
	}

	public function tipo() {
		return $this->belongsTo(Parametrica::class, 'param_tipo_id');
	}

	public function calidad() {
		return $this->belongsTo(Parametrica::class, 'param_calidad_id');
	}

	public function linea() {
		return $this->belongsTo(Linea::class, 'linea_id');
	}

	public function producto_comercializacion()
    {
		return $this->hasOne(ProductoComercializacion::class, 'id', 'producto_id')->select('id','nombre', 'codigo','codigo_alternativo');
    }

}
