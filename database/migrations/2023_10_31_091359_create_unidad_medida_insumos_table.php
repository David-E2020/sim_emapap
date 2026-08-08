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
		Schema::create('insumos.unidad_medida', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('nombre', 100);
			$table->string('abreviatura', 50)->nullable();
			$table->string('abreviatura_global', 50)->nullable();
			$table->string('magnitud', 50)->nullable();
			$table->decimal('equivalencia_kg', 14)->nullable();
			$table->integer('id_unidad')->nullable();
			$table->integer('usr_registrado')->nullable();
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
		Schema::dropIfExists('insumos.unidad_medida');
	}
};
