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
		Schema::create('rrhh.eventual_schedules', function (Blueprint $table) {
			$table->bigIncrements('id');
			// $table->string('name');
			$table->bigInteger('type_hour_id')->unsigned();
			//$table->foreign('type_hour_id')->references('id')->on('rrhh.type_hours'); //PENDIENTE TABLA type_hours
			$table->bigInteger('employee_id')->unsigned();
			$table->foreign('employee_id')->references('id')->on('rrhh.employees'); //empleado solicitante
			$table->date('date');
			$table->char('estado', 1)->default('A');
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
		Schema::dropIfExists('rrhh.eventual_schedules');
	}
};
