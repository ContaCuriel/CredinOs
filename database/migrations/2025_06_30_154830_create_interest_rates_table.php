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
        $table->decimal('rate_value', 5, 2)->unique();
        $table->string('description')->nullable();
        $table->timestamps();
    }); // <-- PUNTO Y COMA AÑADIDO
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_rates');
    }
};
