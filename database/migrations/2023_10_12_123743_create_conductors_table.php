<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConductorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acopio.conductors', function (Blueprint $table) {
            $table->bigIncrements('con_id');
            $table->integer('con_transporte_id');
            $table->foreign('con_transporte_id')->references('trs_id')->on('acopio.transportes');
            $table->string('con_nombre_completo');
            $table->string('con_numero_identificacion')->nullable();
            $table->string('con_direccion')->nullable();
            $table->string('con_telefono')->nullable();
            $table->string('con_categoria')->nullable();
            $table->string('con_numero_licencia')->nullable();
            $table->char('con_estado_registro', 1)->default('A');
            $table->integer('con_usr_registrado');
            $table->foreign('con_usr_registrado')->references('id')->on('public.users');
            $table->integer('con_usr_modificado')->nullable();;
            $table->foreign('con_usr_modificado')->references('id')->on('public.users');
            $table->integer('con_usr_eliminado')->nullable();;
            $table->foreign('con_usr_eliminado')->references('id')->on('public.users');
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
        Schema::dropIfExists('acopio.conductors');
    }
}
