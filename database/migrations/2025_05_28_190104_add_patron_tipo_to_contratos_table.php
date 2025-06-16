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
            // Añadimos la nueva columna después de 'id_empleado'
            // Puedes ajustar el tipo y longitud según necesites.
            // 'fisica' o 'moral'
            $table->string('patron_tipo', 20)->after('id_empleado')->comment('Tipo de patrón: fisica o moral'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn('patron_tipo');
        });
    }
};
