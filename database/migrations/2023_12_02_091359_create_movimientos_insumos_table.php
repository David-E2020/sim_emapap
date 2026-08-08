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
		Schema::create('insumos.movimientos', function (Blueprint $table) {
			$table->bigIncrements('mv_id');
			$table->integer('mv_nro_correlativo');
			$table->integer('mv_tipo_solicitud_id');
            $table->foreign(['mv_tipo_solicitud_id'])->references(['id'])->on('insumos.tipo_documentos_insumos');			
			$table->integer('mv_solicitud_id')->nullable();
			$table->integer('mv_tipo_movimiento_id');
			$table->integer('mv_planta_id')->nullable(); //planta
			$table->foreign('mv_planta_id')->references('id')->on('acopio.plantas');
			$table->integer('mv_origen_id')->nullable(); //alamacen/silo
			$table->foreign('mv_origen_id')->references('id')->on('public.sucursals');
			$table->integer('mv_destino_id')->nullable();
			$table->foreign('mv_destino_id')->references('id')->on('public.sucursals');
			$table->json('mv_datos')->nullable();
			$table->string('mv_tipo', 200)->nullable();
			$table->integer('mv_estado_id')->nullable();
			$table->char('mv_estado', 1)->default('A');
			$table->integer('mv_usr_registrado');
			$table->foreign('mv_usr_registrado')->references('id')->on('public.users');
			$table->integer('mv_usr_modificado')->nullable();
			$table->foreign('mv_usr_modificado')->references('id')->on('public.users');
			$table->integer('mv_usr_eliminado')->nullable();
			$table->foreign('mv_usr_eliminado')->references('id')->on('public.users');
			// $table->boolean('mvi_permitido');
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
		Schema::dropIfExists('insumos.movimientos');
	}
};
