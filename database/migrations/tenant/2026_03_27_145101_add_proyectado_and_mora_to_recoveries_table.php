<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔒 Si la tabla existe (empresas viejas) la modifica. Si no existe (empresas nuevas), la ignora silenciosamente.
        if (Schema::hasTable('recoveries')) {
            Schema::table('recoveries', function (Blueprint $table) {
                if (!Schema::hasColumn('recoveries', 'cobro_proyectado')) {
                    $table->decimal('cobro_proyectado', 15, 2)->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recoveries')) {
            Schema::table('recoveries', function (Blueprint $table) {
                $table->dropColumn('cobro_proyectado');
            });
        }
    }
};