<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractType extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.contract_types';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function contract_type() {
		return $this->belongsTo('App\Models\RRHH\ContractType');
	}
	public function getUrlTipoContrato() {
		$url_contrato = $this->url_contrato ?? '';
		return $url_contrato;
	}
	public function getNombreContrato() {
		$name = $this->name ?? '';
		return $name;
	}
	public function contract_modality() {
		return $this->belongsTo('App\Models\RRHH\ContractModality', 'id_modalidad');
	}
}
