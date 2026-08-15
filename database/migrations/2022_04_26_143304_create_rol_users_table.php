<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rol_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rol_id');
            $table->foreign('rol_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->unsignedBigInteger('usuario_id');
            $table->foreign('usuario_id')->references('id')->on('users')->cascadeOnDelete();
            $table->boolean('estado')->default(true);
            $table->unsignedBigInteger('usr_registrado')->nullable();
            $table->foreign('usr_registrado')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('usr_modificado')->nullable();
            $table->foreign('usr_modificado')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('usr_eliminado')->nullable();
            $table->foreign('usr_eliminado')->references('id')->on('users')->nullOnDelete();
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
        Schema::dropIfExists('rol_users');
    }
}
