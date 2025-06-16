<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- ¡Asegúrate de que esta línea 'use' esté!

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Usamos una sentencia SQL directa para renombrar la columna, evitando el error de sintaxis
        if (Schema::hasColumn('deducciones_empleados', 'status_prestamo')) {
            DB::statement("ALTER TABLE `deducciones_empleados` CHANGE `status_prestamo` `status` VARCHAR(50) NOT NULL DEFAULT 'Activo'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Para revertir, renombramos la columna de vuelta a su nombre original
        if (Schema::hasColumn('deducciones_empleados', 'status')) {
            DB::statement("ALTER TABLE `deducciones_empleados` CHANGE `status` `status_prestamo` VARCHAR(50) NOT NULL DEFAULT 'Activo'");
        }
    }
};