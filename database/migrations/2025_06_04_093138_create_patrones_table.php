<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrones', function (Blueprint $table) {
            $table->id('id_patron');
            $table->string('nombre_comercial');
            $table->string('razon_social');
            $table->string('tipo_persona');
            $table->string('rfc');
            $table->string('curp', 18)->nullable();
            $table->string('registro_patronal', 20)->nullable();
            $table->string('codigo_postal', 5);
            $table->string('regimen_fiscal');
            $table->text('direccion_fiscal')->nullable();
            $table->text('actividad_principal')->nullable();
            $table->string('representante_legal')->nullable();
            $table->string('logo_path')->nullable();

            // Campos para certificados y sellos digitales (CFDI 4.0)
            $table->text('csd_cer_path')->nullable();
            $table->text('csd_key_path')->nullable();
            $table->text('csd_password')->nullable();
            $table->timestamp('csd_expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrones');
    }
};