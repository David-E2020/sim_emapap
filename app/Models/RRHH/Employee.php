<?php

namespace App\Models\RRHH;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model {
	use HasFactory;
	use SoftDeletes;

	protected $table = 'rrhh.employees';
	protected $primaryKey = 'id';
	public $timestamps = true;

	public function contract_modality() {
		return $this->belongsTo('App\Models\RRHH\ContractModality');
	}
	public function contract_type() {
		return $this->belongsTo('App\Models\RRHH\ContractType');
	}
	public function position() {
		return $this->belongsTo('App\Models\RRHH\Position');
	}
	public function city_identity_card() {
		return $this->belongsTo('App\Models\RRHH\City', 'city_identity_card_id');
	}
	public function contribution() {
		return $this->belongsTo('App\Models\RRHH\Contribution');
	}
	public function management() {
		return $this->belongsTo('App\Models\RRHH\Management');
	}
	public function families() {
		return $this->hasMany('App\Models\RRHH\Family')->with('kinship');
	}
	public function academic_trainings() {
		return $this->hasMany('App\Models\RRHH\AcademicTraining');
	}
	public function courses() {
		return $this->hasMany('App\Models\RRHH\Course');
	}
	public function languages() {
		return $this->hasMany('App\Models\RRHH\Language');
	}
	public function packages() {
		return $this->hasMany('App\Models\RRHH\Package');
	}
	public function health_box() {
		return $this->belongsTo('App\Models\RRHH\HealthBox', 'healh_box_id');
	}
	public function country() {
		return $this->belongsTo('App\Models\RRHH\Country');
	}
	public function works() {
		return $this->hasMany('App\Models\RRHH\WorkExperience');
	}
	public function unity() {
		return $this->belongsTo('App\Models\RRHH\Unity');
	}
	public function type_hours() {
		return $this->belongsToMany('App\Models\RRHH\TypeHour', 'employee_type_hours', 'employee_id', 'type_hour_id');
	}
	public function type_hours_employee() {
		return $this->hasMany('App\Models\RRHH\EmployeeTypeHour')->with('type_date');
	}
	public function vacations() {
		return $this->hasMany('App\Models\RRHH\Vacation');
	}
	public function location() {
		return $this->belongsTo('App\Models\Planta');
	}

	public function history() {
		return $this->hasMany('App\Models\RRHH\HistoricoCargo', 'car_employee_id');
	}
	public function getFullName() {
		$first_name = $this->first_name ?? '';
		$second_name = $this->second_name ?? '';
		$last_name = $this->last_name ?? '';
		$mother_last_name = $this->mother_last_name ?? '';
		return $first_name . ' ' . $second_name . ' ' . $last_name . ' ' . $mother_last_name;
	}
	public function getIdContract() {
		$contract_type_id = $this->contract_type_id ?? '';
		return $contract_type_id;
	}
	public function document_type() {
		return $this->belongsTo('App\Models\RRHH\DocumentType');
	}

	public function eventual_schedule() {
		return $this->hasMany('App\Models\RRHH\EventualSchedule', 'employee_id')->with('type_hour');
	}
	public function salary_scale() {
		return $this->belongsTo('App\Models\RRHH\SalaryScale');
	}
}
