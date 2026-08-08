<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeRequest extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.employee_requests';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function request_type() {
		return $this->belongsTo('App\Models\RRHH\RequestType');
	}

	public function approves() {
		return $this->hasMany('App\Models\RRHH\Approve')->with('position')->orderBy('id');
	}

	public function employee() {
		return $this->belongsTo('App\Models\RRHH\Employee', 'employee_id');
	}

	public function storage() {
		return $this->belongsTo('App\Models\Planta', 'planta_id');
	}
}
