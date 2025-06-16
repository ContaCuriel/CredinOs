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
        Schema::create('patrones', function (Blueprint $table) {
            $table->id('id_patron'); // Llave primaria
            $table->string('nombre_comercial', 255)->comment('Nombre para mostrar en UI, ej: CredinOs - Mi Empresa');
            $table->string('razon_social', 255)->unique()->comment('Nombre o Razón Social fiscal');
            $table->enum('tipo_persona', ['fisica', 'moral'])->comment('Física o Moral');
            $table->string('rfc', 13)->unique();
            $table->text('direccion_fiscal')->nullable();
            $table->text('actividad_principal')->nullable();
            $table->string('representante_legal', 255)->nullable()->comment('Solo si es Persona Moral o si aplica');
            $table->string('logo_path')->nullable()->comment('Ruta al archivo del logo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrones');
    }
};
