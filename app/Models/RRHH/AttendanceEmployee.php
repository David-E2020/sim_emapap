<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceEmployee extends Model {
	use HasFactory;
	use SoftDeletes;
	protected $table = 'rrhh.attendance_employees';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public function storage() {
		return $this->belongsTo('App\Models\Planta', 'planta_id');
	}
}
