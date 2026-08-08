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
        Schema::create('insumos.correlativos_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('planta_id');
            $table->integer('codigo_tipo_documento')->nullable();
            $table->integer('gestion')->nullable();
            $table->integer('estado')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('secuencia')->nullable();
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
        Schema::dropIfExists('insumos.correlativos_insumos');
    }
};
