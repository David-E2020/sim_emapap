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
        Schema::create('acopio.capacidades', function (Blueprint $table) {
            $table->id(); 
            $table->integer('cap_campania_id')->nullable();
            $table->integer('cap_programa_id')->nullable();
            $table->integer('cap_planta_id')->nullable();
			$table->foreign('cap_planta_id')->references('id')->on('acopio.plantas');
            $table->integer('cap_punto_id')->nullable();
            $table->integer('cap_tipo_documento_id')->nullable();
            $table->integer('cap_nro_documento')->nullable();
            $table->string('cap_archivo')->nullable();
            $table->integer('cap_punto_unidad_medida_id')->nullable();
            $table->decimal('cap_punto_cantidad', 18, 3)->nullable();
            $table->decimal('cap_punto_saldo', 18, 3)->nullable();
            $table->jsonb('cap_datos')->nullable();
            $table->char('estado', 1)->default('A');
			$table->integer('cap_usr_registrado')->nullable();
			$table->foreign('cap_usr_registrado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('cap_usr_modificado')->nullable();
			$table->foreign('cap_usr_modificado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('cap_usr_eliminado')->nullable();
			$table->foreign('cap_usr_eliminado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('acopio.capacidades');
    }
};
