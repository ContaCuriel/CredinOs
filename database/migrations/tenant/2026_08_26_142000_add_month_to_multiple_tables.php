<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Las mismas tablas donde faltaba 'year' probablemente necesitan 'month'
        $tablas = ['nominas', 'periodos_vacacionales', 'recoveries'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'month')) {
                Schema::table($tabla, function (Blueprint $table) {
                    // Se agrega como entero (ej. 1 para Enero, 12 para Diciembre)
                    $table->integer('month')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = ['nominas', 'periodos_vacacionales', 'recoveries'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'month')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropColumn('month');
                });
            }
        }
    }
};