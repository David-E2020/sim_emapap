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
        Schema::create('insumos.ingresos_detalle_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ingreso_insumo_id')->nullable();
            // $table->foreign(['ingreso_insumo_id'])->references(['id'])->on('insumos.ingresos_insumos');            
            $table->integer('articulo_id')->nullable();
            // $table->foreign(['articulo_id'])->references(['id'])->on('insumos.articulos');
            $table->decimal('cantidad', 14)->nullable();
            $table->decimal('costo', 14)->nullable();
            $table->text('lote')->nullable();
            $table->timestamp('fecha_vencimiento')->nullable();
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
        Schema::dropIfExists('insumos.ingresos_detalle_insumos');
    }
};
