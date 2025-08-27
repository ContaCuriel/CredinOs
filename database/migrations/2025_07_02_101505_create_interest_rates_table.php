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
        if (!Schema::hasTable('interest_rates')) {
            Schema::create('interest_rates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->text('description')->nullable();
                $table->decimal('rate', 8, 4);
                $table->enum('type', ['normal', 'late_fee'])->default('normal');
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
        Schema::dropIfExists('interest_rates');
    }
};
