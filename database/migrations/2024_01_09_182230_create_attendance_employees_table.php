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
		Schema::create('rrhh.attendance_employees', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('employee_id')->unsigned();
			$table->foreign('employee_id')->references('id')->on('rrhh.employees'); //empleado
			$table->bigInteger('biometric_id')->unsigned();
			$table->foreign('biometric_id')->references('id')->on('rrhh.biometrics'); //biometrico
			$table->bigInteger('biometric_code'); //badge number
			$table->date('date');
			$table->time('time');
			$table->time('delayed');
			$table->text('type_module')->nullable();
			$table->text('estado_inicio')->nullable();
			$table->text('ip_biometrico')->nullable();
			$table->integer('planta_id')->nullable();
			$table->foreign('planta_id')->references('id')->on('acopio.plantas');
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
		Schema::dropIfExists('rrhh.attendance_employees');
	}
};
