<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventualSchedule extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.eventual_schedules';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function type_hour() {
		return $this->belongsTo('App\Models\RRHH\TypeHour');
	}
}
