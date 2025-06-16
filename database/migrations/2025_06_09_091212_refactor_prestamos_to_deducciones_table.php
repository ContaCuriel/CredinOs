<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // <-- Importante para actualizar datos existentes

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Renombrar la tabla de 'prestamos_empleados' a 'deducciones_empleados'
        Schema::rename('prestamos_empleados', 'deducciones_empleados');

        // 2. Añadir y modificar columnas en la nueva tabla 'deducciones_empleados'
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // Añadir la columna clave 'tipo_deduccion'
            $table->string('tipo_deduccion', 100)->after('id_empleado')->default('Préstamo');

            // Renombrar 'comentarios' a 'descripcion' para que sea más genérico
            if (Schema::hasColumn('deducciones_empleados', 'comentarios')) {
                $table->renameColumn('comentarios', 'descripcion');
            }

            // Hacer que las columnas específicas de préstamos sean opcionales (nullable)
            $table->decimal('monto_total_prestamo', 10, 2)->nullable()->change();
            $table->integer('plazo_quincenas')->nullable()->change();
            $table->integer('quincenas_pagadas')->nullable()->change();
            $table->decimal('saldo_pendiente', 10, 2)->nullable()->change();
        });

        // 3. Actualizar los registros existentes para asignarles el tipo 'Préstamo'
        // (Asegurarse de que el default ya no lo haga, o forzarlo por si acaso)
        DB::table('deducciones_empleados')->update(['tipo_deduccion' => 'Préstamo']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Estos pasos revierten las acciones de up() en orden inverso

        // 1. Añadir y modificar columnas de vuelta a su estado original
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            $table->dropColumn('tipo_deduccion');

            if (Schema::hasColumn('deducciones_empleados', 'descripcion')) {
                $table->renameColumn('descripcion', 'comentarios');
            }

            // Revertir a no nullable podría fallar si hay datos que no cumplen
            // Por seguridad, se podría dejar como nullable
            $table->decimal('monto_total_prestamo', 10, 2)->nullable(false)->change();
            $table->integer('plazo_quincenas')->nullable(false)->change();
            $table->integer('quincenas_pagadas')->nullable(false)->change();
            $table->decimal('saldo_pendiente', 10, 2)->nullable(false)->change();
        });

        // 2. Renombrar la tabla de vuelta a 'prestamos_empleados'
        Schema::rename('deducciones_empleados', 'prestamos_empleados');
    }
};