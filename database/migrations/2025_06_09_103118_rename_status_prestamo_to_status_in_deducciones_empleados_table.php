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
            // Renombrar la columna de 'status_prestamo' a 'status'
            if (Schema::hasColumn('deducciones_empleados', 'status_prestamo')) {
                $table->renameColumn('status_prestamo', 'status');
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
            if (Schema::hasColumn('deducciones_empleados', 'status')) {
                $table->renameColumn('status', 'status_prestamo');
            }
        });
    }
};
