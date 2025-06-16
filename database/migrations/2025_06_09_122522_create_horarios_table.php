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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id('id_horario');
            $table->string('nombre_horario', 100)->unique();
            $table->text('descripcion')->nullable();

            // Días de la semana
            $table->boolean('lunes')->default(false);
            $table->time('lunes_entrada')->nullable();
            $table->time('lunes_salida')->nullable();

            $table->boolean('martes')->default(false);
            $table->time('martes_entrada')->nullable();
            $table->time('martes_salida')->nullable();

            $table->boolean('miercoles')->default(false);
            $table->time('miercoles_entrada')->nullable();
            $table->time('miercoles_salida')->nullable();

            $table->boolean('jueves')->default(false);
            $table->time('jueves_entrada')->nullable();
            $table->time('jueves_salida')->nullable();

            $table->boolean('viernes')->default(false);
            $table->time('viernes_entrada')->nullable();
            $table->time('viernes_salida')->nullable();

            $table->boolean('sabado')->default(false);
            $table->time('sabado_entrada')->nullable();
            $table->time('sabado_salida')->nullable();

            $table->boolean('domingo')->default(false);
            $table->time('domingo_entrada')->nullable();
            $table->time('domingo_salida')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};