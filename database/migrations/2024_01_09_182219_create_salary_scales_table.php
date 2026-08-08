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
		Schema::create('rrhh.salary_scales', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->bigInteger('category_id')->unsigned();
			$table->foreign('category_id')->references('id')->on('rrhh.categories');
			$table->string('class');
			$table->string('level');
			$table->integer('number');
			$table->decimal('salary', 13, 2);
			$table->decimal('cost', 13, 2);
			$table->decimal('colateral', 13, 2);
			$table->decimal('total', 13, 2);
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
		Schema::dropIfExists('rrhh.salary_scales');
	}
};
