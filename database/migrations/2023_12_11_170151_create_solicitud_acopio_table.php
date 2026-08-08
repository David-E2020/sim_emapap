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
		Schema::create('acopio.solicitud_acopio', function (Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('solicitud_id');
			$table->integer('tipo_solicitud_id');
			$table->integer('tipo_documento_id')->nullable();
			$table->integer('num_documento')->default(0);
			$table->dateTime('fecha_documento')->nullable();
			$table->decimal('monto_documento', 18, 2)->nullable();
			$table->integer('nro')->nullable();;
			$table->integer('origen_id')->nullable();;
			$table->foreign(['origen_id'])->references(['id'])->on('public.sucursals');						
			$table->integer('destino_id')->nullable();;
            $table->foreign(['destino_id'])->references(['id'])->on('public.sucursals');			
			$table->dateTime('fecha_sistema')->nullable();
			$table->dateTime('fecha_solicitud')->nullable();
			$table->dateTime('fecha_verificacion')->nullable();
			$table->dateTime('fecha_recibo')->nullable();
			$table->dateTime('fecha_aprobacion')->nullable();
			$table->char('estado_registro', 1)->default('A');
			$table->integer('estado_id')->default(1);
			$table->string('archivo')->nullable();
			$table->jsonb('data')->nullable();
			$table->integer('usr_origen_id')->references('id')->on('public.users');
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
        Schema::dropIfExists('acopio.solicitud_acopio');
    }
};
