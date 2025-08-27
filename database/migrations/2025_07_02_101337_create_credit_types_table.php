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
        // Verificamos si la tabla NO existe antes de intentar crearla
        if (!Schema::hasTable('credit_types')) {
            Schema::create('credit_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->boolean('is_group_loan')->default(false);
                $table->integer('default_term');
                $table->enum('term_frequency', ['weekly', 'biweekly', 'monthly']);
                $table->decimal('interest_rate', 8, 4);
                $table->decimal('late_interest_rate', 8, 4);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // La lógica de 'down' es simplemente borrar la tabla si existe.
        Schema::dropIfExists('credit_types');
    }
};
