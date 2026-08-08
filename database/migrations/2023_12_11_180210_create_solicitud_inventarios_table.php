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
        Schema::create('inventario.solicitud_inventarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('solicitud_id')->nullable();
            $table->integer('tipo_solicitud_id')->nullable();
            $table->foreign(['tipo_solicitud_id'])->references(['id'])->on('insumos.tipo_documentos_insumos');
            $table->integer('tipo_documento_id')->nullable();
            $table->integer('num_documento')->default(0);
            $table->dateTime('fecha_documento')->nullable();
            $table->decimal('monto_documento', 18, 2)->nullable();
            $table->integer('nro')->nullable();;
            $table->integer('planta_id')->nullable(); //planta
            $table->foreign('planta_id')->references('id')->on('acopio.plantas');
            $table->integer('origen_id')->nullable();;
            $table->foreign(['origen_id'])->references(['id'])->on('public.sucursals');
            $table->integer('destino_id')->nullable();
            $table->foreign(['destino_id'])->references(['id'])->on('public.sucursals');
            $table->integer('lote_id')->nullable();
            $table->foreign(['lote_id'])->references(['id'])->on('inventario.lotes');
            $table->integer('contrato_id')->nullable();
            $table->foreign(['contrato_id'])->references(['id'])->on('inventario.contratos');
            $table->dateTime('fecha_sistema')->nullable();
            $table->dateTime('fecha_solicitud')->nullable();
            $table->dateTime('fecha_verificacion')->nullable();
            $table->dateTime('fecha_recibo')->nullable();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->char('estado_registro', 1)->default('A');
            $table->integer('estado_id')->default(1);
            $table->string('archivo')->nullable();
            $table->jsonb('data')->nullable();
            $table->jsonb('data_usuarios')->default('[]');
            $table->integer('id_logistica')->nullable();
            $table->integer('id_conductor')->nullable();
            $table->foreign('id_conductor')->references('id')->on('public.conductors');
            $table->integer('id_vehiculo')->nullable();
            $table->foreign('id_vehiculo')->references('id')->on('public.transportes');
            $table->bigInteger('movimiento_ingreso_id')->nullable();
            $table->bigInteger('movimiento_salida_id')->nullable();
            $table->integer('usr_origen_id')->nullable()->references('id')->on('public.users');
            $table->integer('usr_destino_id')->nullable()->references('id')->on('public.users');
            $table->integer('usr_entrega_id')->nullable()->references('id')->on('public.users');
            $table->integer('usr_aprobacion_id')->nullable()->references('id')->on('public.users');
            $table->integer('usr_registrado');
            $table->foreign('usr_registrado')->references('id')->on('public.users');
            $table->integer('usr_modificado')->nullable();
            $table->foreign('usr_modificado')->references('id')->on('public.users');
            $table->integer('usr_eliminado')->nullable();
            $table->foreign('usr_eliminado')->references('id')->on('public.users');
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
        //
    }
};
