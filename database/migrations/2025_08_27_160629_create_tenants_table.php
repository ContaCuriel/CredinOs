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
        Schema::create('tenants', function (Blueprint $table) {
            // --- Información del Inquilino ---
            $table->id(); // ID único para cada inquilino
            $table->string('name'); // Nombre de la empresa, ej: "CrediIntegra"

            // --- Dominio para identificar al inquilino ---
            // Este es el campo CLAVE. Lo usaremos para saber qué cliente está haciendo la petición.
            $table->string('domain')->unique(); // ej: "credintegra.localhost" o "credintegra.miempresa.com"

            // --- Detalles de la Conexión a su Base de Datos ---
            // Guardamos aquí las credenciales para que Laravel sepa cómo conectarse
            // a la base de datos específica de este inquilino.
            $table->string('db_database'); // ej: "credintegra_db"
            $table->string('db_host')->default('127.0.0.1');
            $table->string('db_port')->default('3306');
            $table->string('db_username');
            $table->string('db_password')->nullable(); // La contraseña puede ser nula

            $table->timestamps(); // Campos created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};