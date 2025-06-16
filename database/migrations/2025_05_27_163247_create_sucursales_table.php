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
         Schema::create('sucursales', function (Blueprint $table) {
        $table->id('id_sucursal'); // PK autoincremental
        $table->string('nombre_sucursal', 150);
        $table->string('direccion_sucursal', 500)->nullable(); // ->nullable() hace que sea opcional
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sucursales');
    }
};
