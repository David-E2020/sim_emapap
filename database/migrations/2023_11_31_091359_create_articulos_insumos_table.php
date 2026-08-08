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
		Schema::create('insumos.articulos', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->text('identificador_mapeo')->nullable();
			$table->text('nombre_producto');
			$table->text('codigo_alterno')->nullable();
			$table->text('imagen')->nullable();
			$table->text('extimagen')->nullable();
			$table->text('marca')->nullable();
			$table->integer('estado_id')->nullable();
			$table->integer('tipo_material_id')->nullable();
			$table->integer('codigo_partida_id')->nullable();
			$table->integer('linea_id')->nullable();
			$table->foreign('linea_id')->references('id')->on('insumos.lineas');
			$table->integer('sublinea_id')->nullable();
			$table->foreign('sublinea_id')->references('id')->on('insumos.sub_lineas');
			$table->integer('unidad_medida_id')->nullable();
			$table->foreign('unidad_medida_id')->references('id')->on('insumos.unidad_medida');
			$table->integer('producto_id')->nullable();
			$table->foreign('producto_id')->references('id')->on('public.articulos');
			$table->integer('categoria_id')->nullable();
			$table->foreign('categoria_id')->references('id')->on('insumos.categorias');
			$table->integer('usr_registrado');
			$table->foreign('usr_registrado')->references('id')->on('public.users');
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users');
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users');
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
		Schema::dropIfExists('insumos.articulos');
	}
};
