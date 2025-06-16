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
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            // Añade la columna logo_path como una cadena (string) que puede ser nula
            $table->string('logo_path')->nullable()->after('nombre_comercial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->table('tenants', function (Blueprint $table) {
            // Elimina la columna logo_path si se revierte la migración
            $table->dropColumn('logo_path');
        });
    }
};