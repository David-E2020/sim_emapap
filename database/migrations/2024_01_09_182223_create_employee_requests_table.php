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
		Schema::create('rrhh.employee_requests', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->bigInteger('employee_id')->unsigned();
			$table->foreign('employee_id')->references('id')->on('rrhh.employees'); //empleado solicitante
			$table->bigInteger('employee_approve_id')->unsigned();
			$table->foreign('employee_approve_id')->references('id')->on('rrhh.employees'); // persona que tiene la boleta
			$table->bigInteger('request_type_id')->unsigned();
			$table->foreign('request_type_id')->references('id')->on('rrhh.request_types'); // persona que tiene la boleta
			$table->enum('state', ['Pendiente', 'Aprobado', 'Rechazado'])->nullable(); //tipo de solicitud
			$table->boolean('is_archived')->default(false);
			$table->integer('correlative')->nullable();
			$table->date('date')->nullable();
			$table->date('todate')->nullable();
			$table->time('hour_in')->nullable();
			$table->time('hour_out')->nullable();
			$table->string('reason')->nullable();
			$table->string('authorized_name')->nullable();
			$table->string('destiny_place')->nullable();
			$table->string('image_path')->nullable();
			$table->string('value1')->nullable();
			$table->string('value2')->nullable();
			$table->string('value3')->nullable();
			$table->string('value4')->nullable();
			$table->string('value5')->nullable();
			$table->integer('request_day')->nullable();
			$table->integer('planta_id')->nullable();
			$table->foreign('planta_id')->references('id')->on('acopio.plantas'); //unificacion por planta
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
		Schema::dropIfExists('rrhh.employee_requests');
	}
};
