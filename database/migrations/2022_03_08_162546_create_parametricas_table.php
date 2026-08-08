<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametricasTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('parametricas', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->text('param_nombre');
			$table->text('param_codigo')->nullable();
			$table->text('param_descripcion')->nullable();
			$table->bigInteger('param_valor')->nullable();
			$table->text('param_tabla');
			$table->integer('param_usr_registrado');
			$table->foreign('param_usr_registrado')->references('id')->on('public.users');
			$table->integer('param_usr_modificado')->nullable();
			$table->foreign('param_usr_modificado')->references('id')->on('public.users');
			$table->integer('param_usr_eliminado')->nullable();
			$table->foreign('param_usr_eliminado')->references('id')->on('public.users');
			$table->char('param_estado', 1)->default('A');
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
		Schema::dropIfExists('parametricas');
	}
}
