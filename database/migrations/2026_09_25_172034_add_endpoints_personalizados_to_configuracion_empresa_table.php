<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturacion.configuracion_empresa', function (Blueprint $table) {
            $table->jsonb('endpoints_personalizados')->nullable()->after('password_p12');
        });
    }

    public function down(): void
    {
        Schema::table('facturacion.configuracion_empresa', function (Blueprint $table) {
            $table->dropColumn('endpoints_personalizados');
        });
    }
};
