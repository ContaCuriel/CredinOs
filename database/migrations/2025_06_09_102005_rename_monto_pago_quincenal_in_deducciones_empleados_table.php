<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // Renombrar la columna de 'monto_pago_quincenal' a 'monto_quincenal'
            if (Schema::hasColumn('deducciones_empleados', 'monto_pago_quincenal')) {
                $table->renameColumn('monto_pago_quincenal', 'monto_quincenal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // Revertir el renombrado si se hace rollback
            if (Schema::hasColumn('deducciones_empleados', 'monto_quincenal')) {
                $table->renameColumn('monto_quincenal', 'monto_pago_quincenal');
            }
        });
    }
};