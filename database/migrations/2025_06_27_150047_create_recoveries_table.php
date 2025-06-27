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
        // Esta tabla almacenará los registros consolidados de recuperación (ingresos) por mes y sucursal.
        Schema::create('recoveries', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('sucursal_id')->constrained('sucursales', 'id_sucursal');
            
            $table->unsignedInteger('year');
            $table->unsignedTinyInteger('month');

            // Montos totales recuperados/castigados en el periodo
            $table->decimal('capital_recovered', 15, 2)->comment('Capital recuperado de los clientes');
            $table->decimal('interest_collected', 15, 2)->comment('Intereses cobrados (ingreso real)');
            $table->decimal('unrecoverable_amount', 15, 2)->default(0)->comment('Monto de préstamos castigados como incobrables');

            $table->foreignId('user_id')->constrained('users');
            $table->text('notes')->nullable();
            
            $table->timestamps();

            $table->unique(['sucursal_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recoveries');
    }
};
