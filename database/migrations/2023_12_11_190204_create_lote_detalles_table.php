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
		Schema::create('inventario.lote_detalles', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->integer('lote_id')->nullable(); //planta
			//$table->foreign('lote_id')->references('id')->on('inventario.lotes');
            $table->integer('movimiento_id')->nullable();
            $table->foreign('movimiento_id')->references('mv_id')->on('inventario.movimiento_inventarios');

			$table->integer('nro_correlativo')->nullable();
			$table->text('nombre')->nullable();
			$table->text('codigo')->nullable();
			$table->decimal('cantidad', 18, 6)->nullable();
			$table->decimal('saldo', 18, 6)->nullable();
			$table->jsonb('datos')->nullable();
			$table->integer('usr_registrado')->nullable();
			$table->foreign('usr_registrado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->char('estado', 1)->default('A');
			$table->softDeletes();
			$table->timestamps();
		});
	}
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::dropIfExists('inventario.lote_detalles');
	}
};
