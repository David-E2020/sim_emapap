<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenusTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('acopio.menus', function (Blueprint $table) {
			$table->id();
			$table->integer('menu_id')->nullable();
			$table->foreign('menu_id')->references('id')->on('acopio.menus');
			$table->string('icon');
			$table->string('route')->nullable();
			$table->string('label');
			$table->integer('level')->default(0);
			$table->integer('order')->nullable();
			$table->string('file')->nullable();
			$table->boolean('estado')->default(true);
			$table->integer('usr_registrado')->nullable();
			$table->foreign('usr_registrado')->references('id')->on('public.users');
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users');
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users');
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
		Schema::dropIfExists('acopio.menus');
	}
}
