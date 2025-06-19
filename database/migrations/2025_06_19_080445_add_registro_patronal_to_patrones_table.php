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
        Schema::table('patrones', function (Blueprint $table) {
            // Añadimos el nuevo campo después de 'rfc'
            $table->string('registro_patronal')->nullable()->after('rfc');
        });
    }

    public function down(): void
    {
        Schema::table('patrones', function (Blueprint $table) {
            // Esto permite revertir el cambio si fuera necesario
            $table->dropColumn('registro_patronal');
        });
    }
};
