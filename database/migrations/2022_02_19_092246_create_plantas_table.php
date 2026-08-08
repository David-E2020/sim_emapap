<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlantasTable extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::create('plantas', function (Blueprint $table) {
			$table->id();
			$table->string('nombre');
			$table->text('descripcion')->nullable();
			$table->text('codigo')->nullable();
			$table->integer('tipo_acopio_id')->nullable();
			$table->text('municipio')->nullable();
			$table->text('telefono')->nullable();
			$table->text('direccion')->nullable();
			$table->text('capacidad')->nullable();
			$table->integer('departamento_id')->nullable(); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('provincia_id')->nullable(); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('municipio_id')->nullable(); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('localidad_id')->nullable(); //ID SE HEREDA DEL ESQUEMA PUBLIC
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
		Schema::dropIfExists('plantas');
	}
}
