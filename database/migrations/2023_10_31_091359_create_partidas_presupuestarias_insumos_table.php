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
        Schema::create('insumos.partidas_presupuestarias_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('codigo')->nullable();
            $table->string('nombre', 100)->nullable();
            $table->string('descripcion', 1000)->nullable();
            $table->boolean('permitido')->nullable()->default(false);
            $table->integer('user_id')->nullable();
            $table->integer('user_id_modificador')->nullable();
            $table->timestamp('fecha_registro')->nullable();
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
        Schema::dropIfExists('insumos.partidas_presupuestarias_insumos');
    }
};
