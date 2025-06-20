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
        Schema::table('contratos', function (Blueprint $table) {
            // Esta línea añade la nueva columna.
            // Será de tipo texto, podrá ser nula (porque al inicio no habrá archivo)
            // y la colocamos después de la columna 'id_contrato' por orden.
            $table->string('ruta_contrato_firmado')->nullable()->after('id_contrato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            // Esta línea permite eliminar la columna si quisiéramos revertir la migración.
            $table->dropColumn('ruta_contrato_firmado');
        });
    }
};