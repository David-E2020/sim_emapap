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
		Schema::create('inventario.stock_existencia_inventarios', function (Blueprint $table) {
			$table->bigIncrements('sk_id');
			$table->integer('sk_gestion')->nullable();
			$table->integer('sk_articulos_id')->nullable();
			$table->foreign('sk_articulos_id')->references('id')->on('insumos.articulos');
			$table->decimal('sk_cantidad',18,6)->nullable();
			$table->decimal('sk_total',18,6)->nullable();
			$table->integer('sk_planta_id')->nullable();
			$table->foreign(['sk_planta_id'])->references(['id'])->on('acopio.plantas');
			$table->integer('sk_destino_id')->nullable();
			$table->text('sk_lote')->nullable();
			$table->text('sk_fecha_vencimiento')->nullable();
			$table->integer('sk_cantidad_minima')->nullable();
			$table->integer('sk_cantidad_maxima')->nullable();
			$table->integer('sk_cantidad_reservada')->nullable();
			$table->integer('sk_unidad_medida_id')->nullable();
			$table->foreign(['sk_unidad_medida_id'])->references(['id'])->on('insumos.unidad_medida');			
			$table->decimal('sk_precio_unitario')->nullable();
			$table->integer('sk_ubicacion_id')->nullable();
			$table->char('sk_estado', 1)->default('A');
			$table->integer('sk_usr_registrado')->nullable();
			$table->foreign('sk_usr_registrado')->references('id')->on('public.users');
			$table->integer('sk_usr_modificado')->nullable();
			$table->foreign('sk_usr_modificado')->references('id')->on('public.users');
			$table->integer('sk_usr_eliminado')->nullable();
			$table->foreign('sk_usr_eliminado')->references('id')->on('public.users');
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
		Schema::dropIfExists('inventario.stock_existencia_inventarios');
	}
};
