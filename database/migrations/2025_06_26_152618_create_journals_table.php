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
        // Esta tabla almacenará el encabezado de cada póliza contable.
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // Fecha de la póliza
            $table->text('concept'); // Concepto o descripción general
            
            // Relación polimórfica.
            // Esto nos permitirá vincular una póliza a diferentes modelos en el futuro
            // (un Gasto, una Venta, una Nómina, etc.) sin crear más columnas.
            $table->morphs('sourceable'); // Crea 'sourceable_id' y 'sourceable_type'

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
