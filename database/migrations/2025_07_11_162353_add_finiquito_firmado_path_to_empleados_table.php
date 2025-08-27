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
        Schema::table('empleados', function (Blueprint $table) {
            // Añadimos una columna para guardar la ruta del archivo PDF
            // La colocamos después de 'motivo_baja' para mantener el orden
            $table->string('finiquito_firmado_path')->nullable()->after('motivo_baja');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('finiquito_firmado_path');
        });
    }
};
