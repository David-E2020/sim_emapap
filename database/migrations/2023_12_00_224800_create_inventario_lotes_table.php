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
		Schema::create('inventario.inventario_lotes', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->text('codigo');
			$table->text('nombre');
			$table->text('origen');
			$table->integer('almacen_id');
			$table->foreign('almacen_id')->references('id')->on('public.sucursals');
			$table->text('codigo_articulo')->nullable();
			$table->integer('articulo_id')->nullable();
			$table->foreign('articulo_id')->references('id')->on('insumos.articulos');
			$table->text('observacion');
			$table->text('fecha_registro')->nullable();
			$table->decimal('cantidad', 18, 2)->nullable();
			$table->decimal('saldo', 18, 2)->nullable();
			$table->decimal('monto', 14)->nullable();
			$table->string('ticket_de', 20)->nullable();
			$table->integer('ticket_a')->nullable();
			$table->char('estado', 1)->default('A');
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
		Schema::dropIfExists('inventario.inventario_lotes');
	}
};
