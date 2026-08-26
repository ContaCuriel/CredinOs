<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar a la tabla recoveries si existe
        if (Schema::hasTable('recoveries') && !Schema::hasColumn('recoveries', 'capital_recovered')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->decimal('capital_recovered', 15, 2)->default(0)->after('monto');
            });
        }

        // 2. Agregar a la tabla creditos si la consulta pertenecía a esta tabla
        if (Schema::hasTable('creditos') && !Schema::hasColumn('creditos', 'capital_recovered')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->decimal('capital_recovered', 15, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recoveries') && Schema::hasColumn('recoveries', 'capital_recovered')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->dropColumn('capital_recovered');
            });
        }

        if (Schema::hasTable('creditos') && Schema::hasColumn('creditos', 'capital_recovered')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->dropColumn('capital_recovered');
            });
        }
    }
};