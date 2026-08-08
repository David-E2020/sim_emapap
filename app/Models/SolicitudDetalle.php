<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudDetalle extends Model {
	use HasFactory;

	protected $table = "acopio.solicitud_detalles";

	public function producto() {
		return $this->belongsTo(Producto::class, 'producto_id');
	}

	///

	public function getCantidadSolicitadaAttribute() {

		$cantidadSolicitada = $this->attributes['cantidad_solicitada'];
		return number_format((float) $cantidadSolicitada, 0, '.', '');
	}

}
