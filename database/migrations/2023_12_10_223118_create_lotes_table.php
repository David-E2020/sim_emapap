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
	public function up() {
		Schema::create('inventario.lotes', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->text('nombre')->nullable();
		    $table->text('codigo')->nullable();
            $table->integer('planta_id')->nullable(); //planta
            $table->foreign('planta_id')->references('id')->on('acopio.plantas');
			$table->integer('punto_id')->nullable(); //alamacen/silo
            $table->foreign('punto_id')->references('id')->on('public.sucursals');
            $table->integer('campania_id')->nullable();
            $table->integer('programa_id')->nullable();
            $table->integer('producto_id')->nullable();
            $table->foreign('producto_id')->references('id')->on('insumos.articulos');
            $table->integer('sub_producto_id')->nullable();
            $table->foreign('sub_producto_id')->references('id')->on('insumos.articulos');
			$table->decimal('cantidad', 18, 6)->nullable();
            $table->decimal('saldo', 18, 6)->nullable();
            $table->decimal('cantidad_reservada', 18, 6)->nullable();
            $table->jsonb('datos')->nullable();
			$table->integer('usr_registrado')->nullable();
			$table->foreign('usr_registrado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('usr_modificado')->nullable();
			$table->foreign('usr_modificado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->integer('usr_eliminado')->nullable();
			$table->foreign('usr_eliminado')->references('id')->on('public.users'); //ID SE HEREDA DEL ESQUEMA PUBLIC
			$table->char('estado', 1)->default('A');
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
        Schema::dropIfExists('inventario.lotes');
    }
};
