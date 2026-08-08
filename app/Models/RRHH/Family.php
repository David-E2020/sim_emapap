<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Family extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.families';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function kinship() {
		return $this->belongsTo('App\Modesl\RRHH\Kinship');
	}
	public function health_box() {
		return $this->belongsTo('App\Models\RRHH\HealthBox', 'healh_box_id');
	}

}
