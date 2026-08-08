<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeTypeHour extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.employee_type_hours';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function type_date() {
		return $this->hasOne('App\Modesls\RRHH\TypeHour', 'id', 'type_hour_id');
	}
}
