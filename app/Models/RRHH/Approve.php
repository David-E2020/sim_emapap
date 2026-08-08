<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Approve extends Model {
	use HasFactory;
	use SoftDeletes;
	protected $table = 'rrhh.academic_trainings';
	protected $primaryKey = 'id';
	public $timestamps = true;
	public function position() {
		return $this->belongsTo('App\Position');
	}
}
