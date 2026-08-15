<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('usr_usuario')->unique();
            $table->string('password');
            $table->char('usr_estado', 1)->default('A');
            $table->string('usr_cargo_add')->nullable();
            $table->string('usr_archivo')->nullable();
            $table->unsignedBigInteger('usr_externo_id')->nullable();
            $table->unsignedBigInteger('usr_registrado')->nullable();
            $table->unsignedBigInteger('usr_modificado')->nullable();
            $table->unsignedBigInteger('usr_eliminado')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
