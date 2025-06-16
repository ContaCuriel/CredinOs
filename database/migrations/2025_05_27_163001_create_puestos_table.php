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
        Schema::create('puestos', function (Blueprint $table) {
        $table->id('id_puesto'); // PK autoincremental
        $table->string('nombre_puesto', 150);
        $table->decimal('salario_mensual', 10, 2);
        $table->timestamps(); // Crea 'created_at' y 'updated_at' (útil)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puestos');
    }
};
