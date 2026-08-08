<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcopioTable extends Migration {
	/**
	 * Run the migrations.r5t
	 *
	 * @return void
	 */

	public function up() {
		Schema::create('acopio.acopio', function (Blueprint $table) {
			$table->bigIncrements('aco_id');
			$table->integer('aco_nro_correlativo');
			$table->text('aco_codigo');
			$table->integer('aco_tipo_acopio_id')->nullable();
			$table->timestamp('aco_fecha_ingreso');
			$table->timestamp('aco_fecha_salida')->nullable();
			$table->integer('aco_camp_id');
			$table->integer('aco_programa_id');
			$table->integer('aco_productor_id');
			$table->integer('aco_planta_origen_id');
			$table->integer('aco_silo_destino_id')->nullable();
			$table->integer('aco_conductor_id')->nullable();;
			$table->integer('aco_transporte_id')->nullable();
			$table->integer('aco_articulo_id')->nullable();
			$table->integer('aco_unidad_id')->nullable();
			$table->integer('aco_cantidad')->nullable();
			$table->decimal('aco_peso_bruto', 18, 6)->nullable();
			$table->decimal('aco_peso_tara', 18, 6)->nullable();
			$table->decimal('aco_peso_neto', 18, 6)->nullable();
			$table->decimal('aco_peso_liquido', 18, 6)->nullable();
			$table->decimal('aco_peso_liquido_acopio', 18, 6)->nullable();
			$table->decimal('aco_peso_liquido_variable', 18, 6)->nullable();
			//$table->char('aco_estadocupo', 1)->default('C');
			$table->jsonb('aco_valor')->nullable();
			$table->jsonb('aco_parametro_valor')->nullable();			
			$table->jsonb('aco_datos')->nullable();
			$table->jsonb('aco_parametro')->nullable();
			$table->char('estado', 1)->default('A');
			$table->integer('estado_id')->default(1);
			$table->integer('estado_acopio_id')->default(1);
			$table->integer('aco_usr_registrado');
			$table->foreign('aco_usr_registrado')->references('id')->on('public.users');
			$table->integer('aco_usr_modificado')->nullable();
			$table->foreign('aco_usr_modificado')->references('id')->on('public.users');
			$table->integer('aco_usr_eliminado')->nullable();
			$table->foreign('aco_usr_eliminado')->references('id')->on('public.users');
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
		Schema::dropIfExists('acopio.acopio');
	}
}
