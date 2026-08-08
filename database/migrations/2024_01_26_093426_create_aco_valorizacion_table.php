<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcoValorizacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('acopio.aco_valorizacion', function (Blueprint $table) {
            $table->bigIncrements('acv_valorizacion_id');
            $table->integer('acv_campania_id')->nullable();
            $table->integer('acv_programa_id')->nullable();
            $table->integer('acv_region_id')->nullable();
            $table->integer('acv_tipo_acopio_id')->nullable();
            $table->integer('acv_tipo_productor_id')->nullable();
            $table->integer('acv_tipo_pago_id')->nullable();
            $table->text('tacv_amano_prod', 25)->nullable();
            $table->char('acv_estadocupo', 1)->default('C');
            $table->decimal('acv_precio_bs', 18, 2)->nullable();
            $table->jsonb('acv_condicion')->nullable();
            $table->decimal('acv_acp_cantidad_rango', 18, 3)->nullable();
            $table->decimal('acv_acp_rendimiento', 18, 3)->nullable();
            $table->decimal('acv_acp_limite_tolerancia', 18, 3)->nullable();
            $table->decimal('acv_acp_limite_aceptable', 18, 3)->nullable();
            $table->integer('acv_acp_ponderacion')->nullable();
            $table->char('acv_estado', 1)->default('A');
            $table->integer('acv_usr_registrado');
			$table->foreign('acv_usr_registrado')->references('id')->on('public.users');
			$table->integer('acv_usr_modificado')->nullable();
			$table->foreign('acv_usr_modificado')->references('id')->on('public.users');
			$table->integer('acv_usr_eliminado')->nullable();
			$table->foreign('acv_usr_eliminado')->references('id')->on('public.users');
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
        Schema::dropIfExists('acopio.aco_valorizacion');
    }
};
