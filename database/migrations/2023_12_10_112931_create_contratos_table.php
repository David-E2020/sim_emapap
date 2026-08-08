<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('inventario.contratos', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->text('nro_proceso_contrato')->nullable();
			$table->text('nro_proceso_interno')->nullable();
			$table->integer('tipo_producto_id')->nullable();
			$table->integer('programa_id')->nullable();
			$table->integer('articulo_id')->nullable();
			$table->integer('tipo_servicio_id')->nullable();
			$table->foreign('tipo_servicio_id')->references('id')->on('acopio.parametricas');
			$table->date('fecha_inicio');
			$table->date('fecha_final');
			$table->text('origen')->nullable();
			$table->text('destino')->nullable();
			$table->jsonb('data')->nullable();
			$table->integer('distribuidora_id');
			$table->decimal('cantidad', 14)->nullable();
			$table->decimal('saldo', 14)->nullable();
			$table->integer('tipo_contrato_id')->nullable();
			$table->text('observacion')->nullable();
			$table->text('estado')->default('REGISTRADO');
			$table->char('estado_baja', 1)->default('A');
			$table->boolean('regularizacion')->default(false);
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
	public function down()
	{
		Schema::dropIfExists('inventario.contratos');
	}
};
