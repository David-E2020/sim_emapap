<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantaUsuariosTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('planta_usuarios', function (Blueprint $table) {
			$table->id();
			$table->integer('planta_id')->nullable();
			$table->foreign('planta_id')->references('id')->on('acopio.plantas');
			$table->integer('user_id')->nullable();
			$table->foreign('user_id')->references('id')->on('public.users');
			$table->integer('usr_registrado')->nullable();
			$table->foreign('usr_registrado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('nivel_acceso_id')->nullable();
			$table->boolean('estado')->default(true);
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
		Schema::dropIfExists('planta_usuarios');
	}
}
