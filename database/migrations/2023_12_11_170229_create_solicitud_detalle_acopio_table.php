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
        Schema::create('acopio.solicitud_detalle_acopio', function (Blueprint $table) {
            $table->bigIncrements('id');
			$table->integer('solicitud_id');
			$table->foreign('solicitud_id')->references('id')->on('acopio.solicitud_acopio');
            $table->integer('acopio_id');
            $table->foreign('acopio_id')->references('aco_id')->on('acopio.acopio');
            $table->text('codigo_acopio');
			$table->integer('articulo_id');
			$table->foreign('articulo_id')->references('id')->on('insumos.articulos');
			$table->decimal('cantidad', 18, 6)->nullable();
			$table->decimal('cantidad_envio')->nullable();
			$table->decimal('cantidad_aprobacion')->nullable();
			$table->decimal('cantidad_revision')->nullable();
			$table->decimal('descuento', 18, 2)->nullable();
			$table->decimal('precio_compra', 18, 2)->nullable();
			$table->decimal('precio_venta', 18, 2)->nullable();
			$table->text('lote')->nullable();
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
        Schema::dropIfExists('acopio.solicitud_detalle_acopio');
    }
};
