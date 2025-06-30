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
    Schema::create('groups', function (Blueprint $table) {
        $table->id('id_group');
        $table->string('nombre_grupo')->unique();
        $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal');
        // Usaremos 'users' para la tabla de usuarios de Laravel. Asegúrate que tu tabla se llame así.
        $table->foreignId('id_asesor')->constrained('users', 'id');
        $table->enum('status', ['Activo', 'Inactivo', 'Completado'])->default('Activo');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
