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
		Schema::create('inventario.solicitud_detalle_inventarios', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('solicitud_id');
			$table->foreign('solicitud_id')->references('id')->on('inventario.solicitud_inventarios');
			$table->integer('articulo_id');
			$table->foreign('articulo_id')->references('id')->on('insumos.articulos');
			$table->text('tipo')->nullable();
			$table->decimal('cantidad', 18, 6)->nullable();
			$table->decimal('cantidad_salida', 18, 6)->nullable();
			$table->decimal('cantidad_ingreso', 18, 6)->nullable();
			$table->decimal('cantidad_envio', 18, 6)->nullable();
			$table->decimal('cantidad_aprobacion', 18, 6)->nullable();
			$table->decimal('cantidad_revision', 18, 6)->nullable();
			$table->decimal('descuento', 18, 6)->nullable();
			$table->decimal('precio_compra', 18, 2)->nullable();
			$table->decimal('precio_venta', 18, 2)->nullable();
			$table->integer('lote_id')->nullable();
            $table->foreign(['lote_id'])->references(['id'])->on('inventario.lotes');
			$table->text('fec_vencimiento')->nullable();
			$table->char('estado', 1)->default('A');
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
		Schema::dropIfExists('inventario.solicitud_detalle_inventarios');
	}
};
