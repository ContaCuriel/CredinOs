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
        // Esta tabla almacenará los registros consolidados de colocación (desembolsos) por mes y sucursal.
        Schema::create('placements', function (Blueprint $table) {
            $table->id();
            
            // Relación con la sucursal
            $table->foreignId('sucursal_id')->constrained('sucursales', 'id_sucursal');
            
            // Periodo al que corresponde el registro
            $table->unsignedInteger('year'); // Ej: 2025
            $table->unsignedTinyInteger('month'); // Ej: 6 (Junio)

            // Monto total desembolsado en ese periodo y sucursal
            $table->decimal('amount', 15, 2);

            // Quién registró este cierre
            $table->foreignId('user_id')->constrained('users');
            
            $table->text('notes')->nullable(); // Campo para notas adicionales
            
            $table->timestamps();

            // Aseguramos que solo haya un registro por sucursal y periodo.
            $table->unique(['sucursal_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('placements');
    }
};
