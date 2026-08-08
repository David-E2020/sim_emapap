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
        Schema::create('insumos.categorias_articulos_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('categoria_id');
            //$table->foreign(['categoria_id'])->references(['id'])->on('insumos.categorias');
            $table->integer('planta_id');
            //$table->foreign(['planta_id'])->references(['id'])->on('acopio.plantas');            
            $table->integer('articulo_id');
            //$table->foreign(['articulo_id'])->references(['id'])->on('insumos.articulos');
            $table->integer('unidad_medida_id');
           // $table->foreign(['unidad_medida_id'])->references(['id'])->on('insumos.unidad_medida');            
            $table->decimal('cantidad_predefinida', 14)->nullable();
            $table->decimal('porcentaje', 14)->nullable();
            $table->integer('user_id')->nullable();
            $table->foreign(['user_id'])->references(['id'])->on('public.users');            
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
        Schema::dropIfExists('insumos.categorias_articulos_insumos');
    }
};
