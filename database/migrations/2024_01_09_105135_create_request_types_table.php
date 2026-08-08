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
		Schema::create('rrhh.request_types', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('name');
			$table->string('description')->nullable();
			$table->string('version')->nullable();
			$table->string('code')->nullable();
			$table->string('label1')->nullable();
			$table->string('label2')->nullable();
			$table->string('label3')->nullable();
			$table->string('label4')->nullable();
			$table->string('label5')->nullable();
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
		Schema::dropIfExists('rrhh.request_types');
	}
};
