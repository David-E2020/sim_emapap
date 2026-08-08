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
        Schema::create('insumos.ubicaciones_almacen_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('planta_id');
            $table->foreign(['planta_id'])->references(['id'])->on('acopio.plantas');
            $table->integer('ubicacion_id')->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->string('sigla', 10)->nullable();
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
        Schema::dropIfExists('insumos.ubicaciones_almacen_insumos');
    }
};
