<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Este método se ejecuta cuando corres 'php artisan migrate'.
     * Aquí es donde añadimos la nueva columna.
     */
    public function up(): void
    {
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // Añadimos la columna 'fecha_ultimo_descuento' de tipo fecha,
            // que puede ser nula (nullable) y la colocamos después 
            // de la columna 'monto_acumulado' para mantener el orden.
            $table->date('fecha_ultimo_descuento')->nullable()->after('monto_acumulado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Este método se ejecuta si necesitas revertir la migración (ej: con 'migrate:rollback').
     * Aquí le decimos a Laravel cómo deshacer el cambio, es decir, eliminar la columna.
     */
    public function down(): void
    {
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            $table->dropColumn('fecha_ultimo_descuento');
        });
    }
};