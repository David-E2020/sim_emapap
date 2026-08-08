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
		Schema::create('rrhh.positions', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name'); //nombre  del cargo
			$table->string('type_dependency')->nullable();
			$table->string('type')->nullable();
			$table->string('job_title')->nullable();
			$table->bigInteger('unit_id')->nullable();
			$table->foreign('unit_id')->references('id')->on('rrhh.unities'); //unidad
			$table->bigInteger('managament_id')->nullable();
			$table->foreign('managament_id')->references('id')->on('rrhh.managements'); //gerencia
			$table->bigInteger('salary_scale_id')->nullable();
			$table->foreign('salary_scale_id')->references('id')->on('rrhh.salary_scales'); //escala salarial
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
		Schema::dropIfExists('rrhh.positions');
	}
};
