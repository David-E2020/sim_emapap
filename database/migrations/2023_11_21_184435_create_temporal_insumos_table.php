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
        Schema::create('insumos.temporal_insumos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_articulo')->nullable();
            $table->integer('id_usuario')->nullable();
            $table->integer('cantidad')->nullable();
            $table->decimal('costo', 14)->nullable();
            $table->text('lote')->nullable();
            $table->integer('id_ingreso')->nullable();            
            $table->timestamp('fecha_vencimiento')->nullable();
            $table->integer('planta_id')->nullable();            
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
        Schema::dropIfExists('insumos.temporal_insumos');
    }
};
