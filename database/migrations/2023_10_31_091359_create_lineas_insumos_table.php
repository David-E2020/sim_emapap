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
		Schema::create('insumos.lineas', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('codigo', 100)->nullable();
			$table->string('nombre', 120)->nullable();
			$table->string('descripcion')->nullable();
			$table->integer('tipo_prod_id')->nullable();
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
		Schema::dropIfExists('insumos.lineas');
	}
};
