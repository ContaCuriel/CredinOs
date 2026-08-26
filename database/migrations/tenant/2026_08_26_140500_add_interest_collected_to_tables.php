<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar a la tabla recoveries
        if (Schema::hasTable('recoveries') && !Schema::hasColumn('recoveries', 'interest_collected')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->decimal('interest_collected', 15, 2)->default(0);
            });
        }

        // 2. Agregar a la tabla creditos (por si la consulta pertenece a esta tabla)
        if (Schema::hasTable('creditos') && !Schema::hasColumn('creditos', 'interest_collected')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->decimal('interest_collected', 15, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recoveries') && Schema::hasColumn('recoveries', 'interest_collected')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->dropColumn('interest_collected');
            });
        }

        if (Schema::hasTable('creditos') && Schema::hasColumn('creditos', 'interest_collected')) {
            Schema::table('creditos', function (Blueprint $table) {
                $table->dropColumn('interest_collected');
            });
        }
    }
};