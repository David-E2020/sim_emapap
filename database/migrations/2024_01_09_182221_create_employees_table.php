<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('rrhh.employees', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('city_identity_card_id')->unsigned(); //identificación del ci
			$table->bigInteger('document_type_id')->unsigned()->nullable(); //identificación del ci
			$table->bigInteger('country_id')->unsigned()->nullable(); //identificación del ci
			$table->bigInteger('contract_type_id')->unsigned()->nullable(); //tipo de contrato
			$table->bigInteger('contract_modality_id')->unsigned()->nullable(); //tipo de contrato
			$table->bigInteger('management_id')->unsigned()->nullable();
			$table->bigInteger('unit_id')->unsigned()->nullable();
			$table->bigInteger('position_id')->unsigned()->nullable();
			$table->bigInteger('area_id')->unsigned()->nullable();
			$table->bigInteger('healh_box_id')->unsigned()->nullable();
			$table->string('first_name'); //primer nombre
			$table->string('second_name')->nullable(); //segundo nombre
			$table->string('last_name'); //apellido paterno
			$table->string('mother_last_name')->nullable(); //apellido materno
			$table->string('surname_husband')->nullable(); //apellido casada
			$table->date('birth_date')->nullable(); //fecha de nacimiento
			$table->string('identity_card'); //carnet de identidad
			$table->enum('gender', ['M', 'F'])->nullable(); // genero
			$table->boolean('disability')->default(false); // discapacidad
			$table->enum('civil_status', ['C', 'S', 'V', 'D'])->nullable(); //estado civil
			$table->string('tutor')->nullable(); // en caso de tener discapacidad
			$table->date('entry_date')->nullable();
			$table->date('retirement_date')->nullable();
			$table->string('reason')->nullable();
			$table->boolean('retired')->default(false); //jubilado
			$table->string('employee_image_path')->nullable();
			$table->string('has_military_card')->nullable();
			$table->string('military_serial_number')->nullable();
			$table->string('corporate_cell')->nullable();
			$table->string('corporate_email')->nullable();
			//contribution_id
			$table->integer('contribution_id')->nullable();
			$table->foreign('contribution_id')->references('id')->on('rrhh.contributions'); //identificación del ci
			$table->string('cua_nua')->nullable();
			$table->string('profession')->nullable();
			$table->string('address')->nullable();
			$table->string('personal_email')->nullable();
			$table->integer('phone')->nullable();
			$table->string('cellphone')->nullable();
			$table->boolean('curriculum')->default(false);
			$table->string('path_curriculum')->nullable();
			$table->decimal('salary')->nullable(); //give wrong
			$table->bigInteger('biometric_code')->nullable();
			$table->date('disengagement_date')->nullable();
			$table->string('status_employee');
			$table->integer('planta_id')->nullable();
			$table->foreign('planta_id')->references('id')->on('acopio.plantas'); //unificacion por planta
			$table->string('img_profile')->nullable();
			$table->boolean('user_edit')->default(false)->nullable();
			//$table->foreign('city_identity_card_id')->references('id')->on('public.departamentos'); //identificación del ci
			$table->foreign('document_type_id')->references('id')->on('rrhh.document_types'); //titpo de documento
			$table->foreign('country_id')->references('id')->on('public.pais'); //pais
			$table->foreign('contract_type_id')->references('id')->on('rrhh.contract_types'); //tipo decontrato
			$table->foreign('contract_modality_id')->references('id')->on('rrhh.contract_modalities'); //tipo decontrato
			$table->foreign('management_id')->references('id')->on('rrhh.managements'); //tipo decontrato
			$table->foreign('unit_id')->references('id')->on('rrhh.unities'); //tipo decontrato
			$table->foreign('area_id')->references('id')->on('rrhh.areas'); //tipo decontrato
			$table->foreign('position_id')->references('id')->on('rrhh.positions'); //tipo decontrato
			$table->foreign('healh_box_id')->references('id')->on('rrhh.health_boxes');
			$table->integer('usr_registrado')->nullable();
			$table->foreign('usr_registrado')->references('id')->on('public.users');
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users');
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users');
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('rrhh.employees');
	}
};
