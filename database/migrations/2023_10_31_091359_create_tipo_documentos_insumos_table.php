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
        Schema::create('insumos.tipo_documentos_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('tipo_movimiento');
            $table->string('nombre', 100);
            $table->string('descripcion');
            $table->string('codigo', 10);
            $table->integer('usuario_id');
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
        Schema::dropIfExists('insumos.tipo_documentos_insumos');
    }
};
