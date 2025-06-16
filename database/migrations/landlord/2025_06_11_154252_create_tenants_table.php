<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Esta migración se ejecutará en la base de datos 'landlord'
        Schema::connection('landlord')->create('tenants', function (Blueprint $table) {
            $table->id();

            // Nombre de la empresa, para tu referencia
            $table->string('name');

            // El subdominio que se usará en la URL (ej: empresa1.misistema.com)
            $table->string('domain')->unique();

            // Aquí guardaremos el nombre de la base de datos de esta empresa
            $table->string('database');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('tenants');
    }
};
