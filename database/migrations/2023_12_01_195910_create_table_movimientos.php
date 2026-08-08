<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMovimientos extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 **/
	public function up() {
		Schema::create('acopio.movimientos', function (Blueprint $table) {
			$table->bigIncrements('mv_id');
			$table->integer('mv_nro_correlativo')->nullable();
			$table->integer('mv_tipo_acopio_id')->nullable();
			$table->integer('mv_programa_id')->nullable();
			$table->integer('mv_periodo_agricola_id')->nullable();
			$table->integer('mv_acopio_id')->nullable();
			$table->integer('mv_tipo_movimiento_id')->nullable();
			$table->integer('mv_estado_id')->nullable();
			$table->char('mv_estado', 1)->default('A');
			$table->integer('mv_planta_id')->nullable(); //planta
			$table->integer('mv_origen_id')->nullable(); //alamacen/silo
			$table->integer('mv_destino_id')->nullable(); //alamacen/silo
			$table->integer('mv_lote')->nullable();
			$table->string('mv_tipo', 20)->nullable();
			$table->json('mv_datos')->nullable();
			$table->integer('mv_usr_registrado');
			$table->foreign('mv_usr_registrado')->references('id')->on('public.users');
			$table->integer('mv_usr_modificado')->nullable();
			$table->foreign('mv_usr_modificado')->references('id')->on('public.users');
			$table->integer('mv_usr_eliminado')->nullable();
			$table->foreign('mv_usr_eliminado')->references('id')->on('public.users');
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
		Schema::dropIfExists('acopio.movimientos');
	}
}
