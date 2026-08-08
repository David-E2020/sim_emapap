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
		Schema::create('logistica.solicitudes_movimiento_logistica', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('id_orden')->nullable();
			//$table->foreign('id_orden')->references('op_id')->on('public.orden_produccion');
			$table->integer('transportadora_id')->nullable();
			$table->integer('vehiculo_id')->nullable();
			$table->integer('conductor_id')->nullable();
			$table->integer('contrato_id')->nullable();
			$table->text('observacion')->nullable();
			$table->jsonb('data_adicional')->nullable();
			$table->integer('nro_solicitud')->nullable();
			$table->text('codigo_solicitud')->nullable();
			$table->integer('op_tipo_solicitud_orden_id');
			$table->char('estado', 1)->default('A');
			$table->dateTime('fecha_recepcion')->nullable();
			$table->char('estado_recepcion', 1)->default('B');
			$table->integer('usr_recepcion')->nullable();
			$table->foreign('usr_recepcion')->references('id')->on('public.users');
			$table->dateTime('fecha_asignacion')->nullable();
			$table->char('estado_asignacion', 1)->default('B');
			$table->integer('usr_asignacion')->nullable();
			$table->foreign('usr_asignacion')->references('id')->on('public.users');
			$table->dateTime('fecha_envio')->nullable();
			$table->char('estado_envio', 1)->default('B');
			$table->integer('usr_envio')->nullable();
			$table->foreign('usr_envio')->references('id')->on('public.users');
			$table->integer('usr_registrado');
			$table->foreign('usr_registrado')->references('id')->on('public.users');
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users');
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('logistica.solicitudes_movimiento_logistica');
	}
};
