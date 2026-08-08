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
		Schema::create('inventario.movimiento_detalle_inventarios', function (Blueprint $table) {
			$table->bigIncrements('mvd_id');
			$table->integer('mvd_mv_id')->nullable();
			$table->foreign('mvd_mv_id')->references('mv_id')->on('inventario.movimiento_inventarios');
			$table->integer('mvd_articulo_id')->nullable();
			$table->foreign('mvd_articulo_id')->references('id')->on('insumos.articulos');
			$table->decimal('mvd_cantidad', 18, 6)->nullable();
			$table->decimal('mvd_precio_unitario', 14)->nullable();
			$table->integer('mvd_lote_id')->nullable();
			$table->foreign('mvd_lote_id')->references('id')->on('inventario.lotes');
			$table->text('mvd_codigo_lote')->nullable();
			$table->timestamp('mvd_fec_vencimiento')->nullable();
			$table->char('mvd_estado', 1)->default('A');
			$table->integer('usr_registrado')->nullable();
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
		Schema::dropIfExists('inventario.movimiento_detalle_inventarios');
	}
};
