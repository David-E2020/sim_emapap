<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableExistenciasStock extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('acopio.stock_existencias', function (Blueprint $table) {
			$table->bigIncrements('sk_id');
			$table->integer('sk_articulos_id');
			$table->foreign('sk_articulos_id')->references('id')->on('insumos.articulos');
			$table->decimal('sk_cantidad', 18, 6)->nullable();
			$table->integer('sk_destino_id'); //Sucursal //almacen
			$table->text('sk_lote')->nullable();
			$table->text('sk_fec_vencimiento')->nullable();
			$table->integer('sk_tipo_acopio')->nullable();
			$table->char('sk_estado', 1)->default('A');
			$table->integer('sk_usr_registrado');
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
		Schema::dropIfExists('acopio.stock_existencias');
	}
}
