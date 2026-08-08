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
		Schema::create('rrhh.families', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('employee_id')->unsigned(); // id del empeleado
			$table->bigInteger('kinship_id')->unsigned();
			$table->string('first_name'); //primer nombre
			$table->string('second_name'); //segundo nombre
			$table->string('last_name'); //apellido paterno
			$table->string('mother_last_name'); //apellido materno
			$table->string('surname_husband')->nullable(); //apellido casada
			$table->boolean('disability')->default(false); // discapacidad
			$table->boolean('is_reference')->default(false);
			$table->text('phone')->nullable();
			$table->text('cellphone')->nullable();
			$table->date('birth_date')->nullable();
			$table->text('age')->nullable();
			$table->bigInteger('health_box_id')->nullable();
			$table->text('number_healt_box')->nullable();
			$table->boolean('has_vaccine')->default(false);
			$table->foreign('employee_id')->references('id')->on('rrhh.employees');
			$table->foreign('kinship_id')->references('id')->on('rrhh.kinships');
			$table->foreign('health_box_id')->references('id')->on('rrhh.health_boxes');
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
		Schema::dropIfExists('rrhh.families');
	}
};
