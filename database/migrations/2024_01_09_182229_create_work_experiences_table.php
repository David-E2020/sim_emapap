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
		Schema::create('rrhh.work_experiences', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('employee_id')->unsigned();
			$table->foreign('employee_id')->references('id')->on('rrhh.employees'); //empleado solicitante
			$table->date('date');
			$table->string('institution');
			$table->string('position');
			$table->string('phone');
			$table->string('month_start')->nullable();
			$table->string('month_finish')->nullable();
			$table->integer('year_start')->nullable();
			$table->integer('year_finish')->nullable();
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
		Schema::dropIfExists('rrhh.work_experiences');
	}
};
