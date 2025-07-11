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
        Schema::create('interest_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('description')->nullable();

            // Tasa de interés (anual, sin IVA)
            // Se guardará como porcentaje, ej: 90.5 para 90.5%
            $table->decimal('rate', 8, 4); 

            // Define si la tasa es para el interés normal o el moratorio
            $table->enum('type', ['normal', 'late_fee'])->default('normal');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_rates');
    }
};