<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcoParametroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    
    public function up()
    {
        Schema::create('acopio.aco_parametro', function (Blueprint $table) {
            $table->bigIncrements('acp_id');
            $table->integer('acp_tipo_analisis_id')->nullable();
            $table->integer('acp_tipo_id')->nullable();
            $table->integer('acp_programa_id')->nullable();
            $table->integer('acp_periodo_id')->nullable();
            $table->text('acp_nombre')->nullable();
            $table->text('acp_categoria')->nullable();
            $table->text('acp_descripcion')->nullable();
            $table->decimal('acp_valor_min_admisible', 18, 3)->nullable();
            $table->decimal('acp_valor_admisible', 18, 3)->nullable();
            $table->decimal('acp_valor_base', 18, 3)->nullable();
            $table->decimal('acp_valor_base_excedente', 18, 3)->nullable();
            $table->decimal('acp_valor_castigo', 18, 3)->nullable();
            $table->decimal('acp_cantidad_rango', 18, 3)->nullable();
            $table->decimal('acp_rendimiento', 18, 3)->nullable();
            $table->decimal('acp_limite_tolerancia', 18, 3)->nullable();
            $table->decimal('acp_limite_aceptable', 18, 3)->nullable();
            $table->integer('acp_ponderacion')->nullable();
            $table->integer('acp_formula')->nullable();
            $table->boolean('acp_descuento')->default('true');
			$table->jsonb('aco_datos')->nullable();;         
            $table->char('acp_estado', 1)->default('A');
            $table->integer('acp_usr_registrado');
			$table->foreign('acp_usr_registrado')->references('id')->on('public.users');
			$table->integer('acp_usr_modificado')->nullable();
			$table->foreign('acp_usr_modificado')->references('id')->on('public.users');
			$table->integer('acp_usr_eliminado')->nullable();
			$table->foreign('acp_usr_eliminado')->references('id')->on('public.users');
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
        Schema::dropIfExists('acopio.aco_parametro'); 
    }
}
