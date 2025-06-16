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
            // Usamos TEXT para permitir más caracteres y lo hacemos nullable (opcional)
            // Lo añadimos después de 'fecha_fin' o donde prefieras
            $table->text('cartas_recomendacion_texto')->nullable()->after('fecha_fin')->comment('Texto para cartas de recomendación, nombres de empresas, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            $table->dropColumn('cartas_recomendacion_texto');
        });
    }
};
