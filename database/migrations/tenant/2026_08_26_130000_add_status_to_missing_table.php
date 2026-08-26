<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tabla = 'empleados'; // 👈 Cambia por la tabla correspondiente si es otra

        if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'status')) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->string('status')->default('activo');
            });
        }
    }

    public function down(): void
    {
        $tabla = 'empleados';
        if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'status')) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};