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
            // Añadimos las nuevas columnas después de 'fecha_nacimiento'
            // Hacemos los campos nullable() ya que podrían no ser obligatorios siempre o al inicio.
            $table->string('nacionalidad', 100)->nullable()->after('fecha_nacimiento');
            $table->string('sexo', 20)->nullable()->after('nacionalidad'); // Ej: Masculino, Femenino, Otro
            $table->string('estado_civil', 50)->nullable()->after('sexo'); // Ej: Soltero(a), Casado(a), etc.
        });
    }

    /**
     * Reverse the migrations.
     */
     public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['nacionalidad', 'sexo', 'estado_civil']);
        });
    }
};
