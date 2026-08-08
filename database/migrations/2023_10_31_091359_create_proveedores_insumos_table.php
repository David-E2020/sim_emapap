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
        Schema::create('insumos.proveedores_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombres')->nullable();
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('NIT')->nullable();
            $table->string('razon_social')->nullable();
            $table->string('informacion_contacto')->nullable();
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
        Schema::dropIfExists('insumos.proveedores_insumos');
    }
};
