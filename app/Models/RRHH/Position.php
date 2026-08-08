<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.positions';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function management() {
		return $this->belongsTo('App\Models\RRHH\Management');
	}

	public function unity() {
		return $this->belongsTo('App\Models\RRHH\Unity', 'unit_id');
	}

	public function salary_scale() {
		return $this->belongsTo('App\Models\RRHH\SalaryScale');
	}
}
