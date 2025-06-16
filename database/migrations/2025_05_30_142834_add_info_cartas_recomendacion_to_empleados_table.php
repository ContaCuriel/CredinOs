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
            // Usamos text para permitir más información, nullable porque es opcional.
            // Puedes cambiar 'after' para ubicar la columna donde prefieras en la tabla.
            $table->text('info_cartas_recomendacion')->nullable()->after('contacto_emerg_telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('info_cartas_recomendacion');
        });
    }
};
