<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acopio.transportes', function (Blueprint $table) {
            $table->bigIncrements('trs_id');
            $table->string('trs_placa')->nullable();
            $table->string('trs_marca')->nullable();
            $table->string('trs_modelo')->nullable();
            $table->string('trs_color')->nullable();
            $table->string('trs_tipo')->nullable();
            $table->string('trs_chasis')->nullable();
            $table->string('trs_soat')->nullable();
            $table->string('trs_inspection')->nullable();
            $table->char('trs_estado', 1)->default('A');
            $table->integer('trs_usr_registrado');
            $table->foreign('trs_usr_registrado')->references('id')->on('public.users');
            $table->integer('trs_usr_modificado')->nullable();;
            $table->foreign('trs_usr_modificado')->references('id')->on('public.users');
            $table->integer('trs_usr_eliminado')->nullable();;
            $table->foreign('trs_usr_eliminado')->references('id')->on('public.users');
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
        Schema::dropIfExists('acopio.transportes');
    }
}
