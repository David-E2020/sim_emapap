<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableMovimientosDetalles extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('acopio.movimiento_detalles', function (Blueprint $table) {
			$table->bigIncrements('mvd_id');
			$table->integer('mvd_mv_id')->nullable();
			$table->foreign('mvd_mv_id')->references('mv_id')->on('acopio.movimientos');
			$table->integer('mvd_articulo_id');
			$table->foreign('mvd_articulo_id')->references('id')->on('insumos.articulos');
			$table->decimal('mvd_cantidad', 18, 6)->nullable();
			$table->text('mvd_lote')->nullable();
			$table->timestamp('mvd_fec_vencimiento')->nullable();
			$table->char('mvd_estado', 1)->default('A');
			$table->integer('mvd_usr_registrado')->nullable();
			$table->foreign('mvd_usr_registrado')->references('id')->on('public.users');
			$table->integer('mvd_usr_modificado')->nullable();
			$table->foreign('mvd_usr_modificado')->references('id')->on('public.users');
			$table->integer('mvd_usr_eliminado')->nullable();
			$table->foreign('mvd_usr_eliminado')->references('id')->on('public.users');
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
		Schema::dropIfExists('acopio.movimientos_detalles');
	}
}
