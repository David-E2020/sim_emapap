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
        Schema::create('insumos.solicitudes_detalle_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('num_secuencia')->nullable();
            $table->integer('cantidad_solicitada')->nullable();
            $table->integer('cantidad_entregada')->nullable();
            $table->integer('solicitud_id')->nullable();
            //$table->foreign(['solicitud_id'])->references(['id'])->on('insumos.solicitudes_insumos');           
            $table->integer('articulo_id')->nullable();
            $table->foreign(['articulo_id'])->references(['id'])->on('insumos.articulos');
            $table->foreign('solicitud_id')
                ->references('id')
                ->on('insumos.solicitudes_insumos')
                ->onDelete('cascade'); // Configura ON DELETE CASCADE
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insumos.solicitudes_detalle_insumos');
    }
};

  