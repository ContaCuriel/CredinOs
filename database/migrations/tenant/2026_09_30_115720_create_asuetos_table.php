<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('asuetos')) {
            Schema::create('asuetos', function (Blueprint $table) {
                $table->id('id_asueto');
                $table->string('nombre');
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                // Si es nulo, significa que aplica para TODAS las sucursales
                $table->unsignedBigInteger('id_sucursal')->nullable(); 
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('asuetos')) {
            Schema::dropIfExists('asuetos');
        }
    }
};