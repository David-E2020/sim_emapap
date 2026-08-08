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
		Schema::create('insumos.solicitudes_insumos', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('nombre_solicitante', 100)->nullable();
			$table->string('cargo_solicitante', 100)->nullable();
			$table->integer('numero_acta')->nullable();
			$table->string('observaciones')->nullable();
			$table->integer('entrada_salida')->nullable();
			$table->string('numero_referencia')->nullable();
			$table->string('total_items')->nullable();
			$table->decimal('total_costo', 14)->nullable();
			$table->integer('user_id')->nullable();
			$table->integer('estado_id');
			$table->integer('planta_id_origen')->nullable();
			$table->integer('planta_id_destino')->nullable();
			$table->integer('tipo_solicitud_id')->nullable();
			$table->integer('numero_documento')->nullable();
			$table->timestamp('fecha_registro')->nullable();
			$table->timestamp('fecha_atencion')->nullable();
			$table->timestamp('fecha_documento')->nullable();
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
		Schema::dropIfExists('insumos.solicitudes_insumos');
	}
};
