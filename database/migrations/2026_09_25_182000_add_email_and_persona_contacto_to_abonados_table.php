<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comercial.abonados', function (Blueprint $table) {
            if (!Schema::hasColumn('comercial.abonados', 'email')) {
                $table->string('email', 100)->nullable()->after('celular');
            }
            if (!Schema::hasColumn('comercial.abonados', 'persona_contacto')) {
                $table->string('persona_contacto', 150)->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercial.abonados', function (Blueprint $table) {
            if (Schema::hasColumn('comercial.abonados', 'persona_contacto')) {
                $table->dropColumn('persona_contacto');
            }
            if (Schema::hasColumn('comercial.abonados', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
