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
		Schema::create('inventario.contrato_detalles', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->text('tipo_contrato');
			$table->integer('contrato_id');
			$table->foreign('contrato_id')->references('id')->on('inventario.contratos');
			$table->string('detalle')->nullable();
			$table->decimal('cantidad', 18, 6)->nullable();
			$table->decimal('monto_global', 18, 2)->nullable();
			$table->string('observaciones')->nullable();
			$table->integer('unidad_medida_id');
			$table->text('origen');
			$table->text('destino');
			$table->decimal('volumen_estimado', 18, 2)->nullable();
			$table->decimal('flete', 18, 2)->nullable();
			$table->integer('estibaje_id')->nullable();
			$table->integer('plazo');
			$table->jsonb('data')->nullable();
			$table->text('estado')->default('REGISTRADO');
			$table->char('estado_baja', 1)->default('A');
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
		Schema::dropIfExists('inventario.contrato_detalles');
	}
};
