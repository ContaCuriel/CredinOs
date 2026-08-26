<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Lista de tablas probables que necesitan sucursal_id
        $tablas = ['empleados', 'users', 'creditos', 'recoveries', 'clientes', 'nominas', 'gastos'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'sucursal_id')) {
                Schema::table($tabla, function (Blueprint $table) {
                    // La creamos nullable para que no marque error con registros que ya existan
                    $table->unsignedBigInteger('sucursal_id')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = ['empleados', 'users', 'creditos', 'recoveries', 'clientes', 'nominas', 'gastos'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'sucursal_id')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->dropColumn('sucursal_id');
                });
            }
        }
    }
};