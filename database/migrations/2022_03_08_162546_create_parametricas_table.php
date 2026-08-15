<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametricasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parametricas', function (Blueprint $table) {
            $table->id();
            $table->string('param_nombre');
            $table->string('param_codigo')->nullable();
            $table->text('param_descripcion')->nullable();
            $table->bigInteger('param_valor')->default(0);
            $table->string('param_tabla');
            $table->unsignedBigInteger('param_usr_registrado')->nullable();
            $table->foreign('param_usr_registrado')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('param_usr_modificado')->nullable();
            $table->foreign('param_usr_modificado')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('param_usr_eliminado')->nullable();
            $table->foreign('param_usr_eliminado')->references('id')->on('users')->nullOnDelete();
            $table->char('param_estado', 1)->default('A');
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
        Schema::dropIfExists('parametricas');
    }
}
