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
    Schema::create('clientes', function (Blueprint $table) {
        $table->id('id_cliente');
        $table->string('nombre');
        $table->string('apellido_paterno');
        $table->string('apellido_materno');
        $table->string('curp')->unique()->nullable();
        $table->string('rfc')->unique()->nullable();
        $table->string('telefono_celular', 20)->nullable();
        $table->string('email')->unique()->nullable();

        // Campos de dirección
        $table->string('calle')->nullable();
        $table->string('numero')->nullable();
        $table->string('colonia')->nullable();
        $table->string('codigo_postal', 10)->nullable();
        $table->string('municipio')->nullable();
        $table->string('estado')->nullable();

        // Relación con sucursal
        $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal');
        
        $table->timestamps();
        $table->softDeletes(); // Para borrado lógico
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
