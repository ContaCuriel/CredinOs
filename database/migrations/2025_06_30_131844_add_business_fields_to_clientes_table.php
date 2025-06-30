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
    Schema::table('clientes', function (Blueprint $table) {
        // Añadimos los campos después de la columna 'email'
        $table->string('ocupacion')->nullable()->after('email');
        $table->string('nombre_negocio')->nullable()->after('ocupacion');
        $table->string('giro_negocio')->nullable()->after('nombre_negocio');
        $table->integer('antiguedad_negocio')->nullable()->after('giro_negocio');
        $table->decimal('ingresos_mensuales', 10, 2)->nullable()->after('antiguedad_negocio');
        $table->decimal('gastos_mensuales', 10, 2)->nullable()->after('ingresos_mensuales');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            //
        });
    }
};
