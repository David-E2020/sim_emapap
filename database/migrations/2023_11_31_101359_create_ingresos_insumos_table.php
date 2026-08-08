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
        Schema::create('insumos.ingresos_insumos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('planta_id');
            // $table->foreign(['planta_id'])->references(['id'])->on('acopio.plantas');            
            $table->integer('proveedor_id')->nullable();
            // $table->foreign(['proveedor_id'])->references(['id'])->on('insumos.proveedores_insumos');            
            $table->integer('user_id');
             $table->foreign(['user_id'])->references(['id'])->on('public.users');
            $table->string('ruta_adjunto')->nullable();
            $table->string('numero_proceso')->nullable();
            $table->timestamp('fecha_factura')->nullable();
            $table->string('motivo')->nullable();
            $table->decimal('costo_total', 14)->nullable();
            $table->integer('numero_acta')->nullable();
            $table->integer('tipo_ingreso')->nullable();
            // $table->foreign(['tipo_ingreso'])->references(['id'])->on('insumos.tipo_documentos_insumos');            
            $table->integer('numero_documento')->nullable();
            $table->integer('estado_id')->nullable();
            $table->timestamp('fecha_acta')->nullable();
            $table->string('observaciones')->nullable();
            $table->timestamp('fecha_ingreso')->nullable();
            $table->string('c31_preventivo', 50)->nullable();
            $table->integer('lote_id')->nullable();
            $table->integer('exclusivo_gerencia_id')->nullable();
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
        Schema::dropIfExists('insumos.ingresos_insumos');
    }
};
