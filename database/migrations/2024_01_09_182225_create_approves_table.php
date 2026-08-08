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
		Schema::create('rrhh.approves', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('employee_request_id')->unsigned();
			$table->foreign('employee_request_id')->references('id')->on('rrhh.employee_requests'); //empleado solicitante
			$table->bigInteger('position_id')->unsigned();
			$table->foreign('position_id')->references('id')->on('rrhh.positions'); //cargo destinado a aprovacion
			$table->enum('state', ['Pendiente', 'Aprobado', 'Rechazado']); //estado
			$table->date('date');
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
		Schema::dropIfExists('rrhh.approves');
	}
};
