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
        Schema::create('credit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            
            // Define si el crédito es para individuos o grupos
            $table->boolean('is_group_loan')->default(false);

            // Plazo (en semanas, quincenas, meses)
            $table->integer('default_term'); 
            $table->enum('term_frequency', ['weekly', 'biweekly', 'monthly']);

            // Tasa de interés moratoria y normal (anual, sin IVA)
            // Se guardará como porcentaje, ej: 90.5 para 90.5%
            $table->decimal('interest_rate', 8, 4); 
            $table->decimal('late_interest_rate', 8, 4);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_types');
    }
};