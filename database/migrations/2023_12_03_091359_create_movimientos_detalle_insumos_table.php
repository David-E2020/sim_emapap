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
		Schema::create('insumos.movimientos_detalles', function (Blueprint $table) {
			$table->bigIncrements('mvd_id');
			$table->integer('mvd_mv_id');
			$table->foreign('mvd_mv_id')->references('mv_id')->on('insumos.movimientos');
			$table->integer('mvd_articulo_id');
			$table->foreign('mvd_articulo_id')->references('id')->on('insumos.articulos');
			$table->decimal('mvd_cantidad', 18, 6)->nullable();
			$table->decimal('mvd_precio_unitario', 14)->nullable();
			$table->integer('mvd_lote_id')->nullable();
			$table->timestamp('mvd_fec_vencimiento')->nullable();
			$table->text('mvd_codigo_lote')->nullable();
			$table->char('mvd_estado', 1)->default('A');
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
		Schema::dropIfExists('insumos.movimientos_detalles');
	}
};
