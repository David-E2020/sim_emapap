<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PuntoVenta extends Model {
	use HasFactory;
	use SoftDeletes;
	protected $table = "acopio.punto_ventas";
	protected $fillable = ['sucursal_id', 'param_tipo_punto_venta_id', 'municipio', 'telefono', 'direccion', 'nombre', 'codigo'];

	protected $appends = ['fecha_vigencia_cufd', 'fecha_ultimo_cufd', 'cufd_valido', 'fecha_vigencia_cuis', 'loader', 'fecha_cudf_inicio', 'fecha_cudf_fin'];

	public function sucursal() {
		return $this->belongsTo(Sucursal::class); //mvd_mv_id
	}

	public function getFechaCudfInicioAttribute() {

		$fecha_ = $this->attributes['cufd_fecha'];
		$fechaCufd_ = new Carbon($fecha_);
		$resp = $fechaCufd_->format('d-m-Y H:i:s');

		return $resp;
	}

	public function getFechaCudfFinAttribute() {
		/*
			        $fecha_ = $this->attributes['cufd_fecha'];
			        $fechaCufd_ = new Carbon($fecha_);

		*/
		/*
			        $now = carbon::now();

			        $date = "2016-09-17 11:00:00";
			$datework = new Carbon($date);

			      $resp=  $fechaCufd_->diffInYears(now());
		*/

		// $resp=   $fechaCufd_->subHours(24);

		$fecha_ = $this->attributes['cufd_fecha'];
		$fechaCufd_ = new Carbon($fecha_);
		$resp = $fechaCufd_->format('d-m-Y H:i:s');

		return $resp; // $resp->format('d-m-Y H:i:s');
	}

	public function getCufdValidoAttribute() {
		Carbon::setLocale('es');
		$resp = null;
		try {
			//code...
			$fecha_ = $this->attributes['cufd_fecha'];
			$fechaCufd_ = new Carbon($fecha_);
			$fechaActual = new Carbon();
			$resp = $fechaActual < $fechaCufd_;
		} catch (\Throwable $th) {
			//throw $th;
		}
		return $resp;
	}

	public function getFechaVigenciaCuisAttribute() {
		Carbon::setLocale('es');
		$resp = null;
		try {
			$fecha_ = $this->attributes['cuis_fecha'];
			if ($fecha_ != null) {
				$date = new Carbon($fecha_);
				$d = $date->diffForHumans();
				$resp = 'Expira ' . $d;
			} else {
				$resp = 'solicitar CUIS';
			}
		} catch (\Throwable $th) {
		}

		return $resp;
	}

	public function getFechaVigenciaCufdAttribute() {
		Carbon::setLocale('es');
		$resp = null;
		try {
			//code...
			$fecha_ = $this->attributes['cufd_fecha'];

			$fechaCufd_ = new Carbon($fecha_);
			$fechaActual = new Carbon();

			if ($fecha_ != null) {
				$date = new Carbon($fecha_);
				$d = $date->diffForHumans();
				$resp = ($fechaActual < $fechaCufd_) ? 'Expira ' . $d : 'Ya expiro ' . $d;
			} else {
				$resp = 'solicitar CUFD';
			}
		} catch (\Throwable $th) {
		}

		return $resp;
	}

	public function getFechaUltimoCufdAttribute() {

		$fecha_ = $this->attributes['updated_at'];
		$date = new Carbon($fecha_);

		$date2 = $date->addMinutes(3);

		$resp = $date2->format('Y-m-d H:i:s');
		return $resp;
	}

	public function getLoaderAttribute() {
		return array('cuis' => false, 'cufd' => false);
	}
}
