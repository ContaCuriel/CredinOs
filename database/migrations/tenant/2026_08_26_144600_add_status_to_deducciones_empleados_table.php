<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deducciones_empleados') && !Schema::hasColumn('deducciones_empleados', 'status')) {
            Schema::table('deducciones_empleados', function (Blueprint $table) {
                // Lo ponemos como 'Activo' por defecto para que coincida con tu consulta
                $table->string('status')->default('Activo');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('deducciones_empleados') && Schema::hasColumn('deducciones_empleados', 'status')) {
            Schema::table('deducciones_empleados', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};