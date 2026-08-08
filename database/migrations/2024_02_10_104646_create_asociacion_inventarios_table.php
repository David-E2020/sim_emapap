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
        Schema::create('inventario.asociaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre_asociacion', 100)->nullable();
            $table->string('nombre_representante',100)->nullable();
            $table->integer('numero_celular')->nullable();
            $table->integer('departamento_id')->nullable();
            $table->json('data')->nullable();
            $table->integer('usr_registrado')->nullable();
            $table->foreign('usr_registrado')->references('id')->on('public.users');
            $table->integer('usr_modificado')->nullable();
            $table->foreign('usr_modificado')->references('id')->on('public.users');
            $table->integer('usr_eliminado')->nullable();
            $table->foreign('usr_eliminado')->references('id')->on('public.users');
            $table->char('estado', 1)->default('A');
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
        Schema::dropIfExists('inventario.asociaciónes');
    }
};