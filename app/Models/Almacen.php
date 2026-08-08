<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model {
	use HasFactory;
	protected $table  = "public.almacen";
	protected $primaryKey = 'id';
	public function sucursal() {
		return $this->hasOne('App\Models\Sucursal', 'id', 'sucursal_id')->select('id', 'id_sucursal', 'codigo', 'departamento_id', 'provincia_id', 'municipio_id', 'localidad_id', 'nombre', 'direccion', 'estado', 'estado_baja', 'tipo_documento_sector_id', 'regional_precio_id');
	}
	public function loginSubsidio() {
		return $this->belongsToMany('App\Models\Subsidio\LoginSubsidio',
							'App\Models\Subsidio\LoginSubsidioAlmacen',
							 'almacen_id', 'login_usuario_id')
					->where('login_subsidio.estado','A')
					->where('almacen_login_sedem.estado','A');
	}

}
