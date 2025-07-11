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
    $table->string('name')->unique(); // Ej: "Grupal Semanal R16", "Individual Comercial"
    $table->integer('default_term'); // Ej: 16 (semanas)
    $table->text('description')->nullable();
    $table->boolean('requires_contract')->default(true);
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
