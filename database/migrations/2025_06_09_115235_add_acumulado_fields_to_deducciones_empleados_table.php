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
            // Columna para guardar el total que se ha ido descontando y ahorrando
            if (!Schema::hasColumn('deducciones_empleados', 'monto_acumulado')) {
                $table->decimal('monto_acumulado', 10, 2)->nullable()->default(0.00)->after('status');
            }

            // Sobrescribimos la columna quincenas_aplicadas si existe para asegurarnos de que sea nullable
            // ya que ahora no solo aplica a préstamos.
            if (Schema::hasColumn('deducciones_empleados', 'quincenas_pagadas')) {
                $table->integer('quincenas_pagadas')->nullable()->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            if (Schema::hasColumn('deducciones_empleados', 'monto_acumulado')) {
                $table->dropColumn('monto_acumulado');
            }

            // Aquí podríamos revertir el cambio a quincenas_pagadas, pero por simplicidad
            // y seguridad de los datos existentes, no lo haremos en el rollback.
        });
    }
};