<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Usamos json (o jsonb en PostgreSQL) para guardar todas las reglas sin crear decenas de columnas
            $table->json('configuracion_nomina')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('configuracion_nomina');
        });
    }
};