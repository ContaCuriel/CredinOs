<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lista de tablas probables que necesitan la columna 'year'
        // Si al revisar tu log de errores ves que falló en otra tabla, agrégala aquí adentro:
        $tablas = ['nominas', 'periodos_vacacionales', 'recoveries'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'year')) {
                Schema::table($tabla, function (Blueprint $table) {
                    // Se agrega como entero (ej. 2026) y nullable para no afectar datos previos
                    $table->integer('year')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = ['nominas', 'periodos_vacacionales', 'recoveries'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'year')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropColumn('year');
                });
            }
        }
    }
};