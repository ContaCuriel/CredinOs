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
        Schema::create('prestamos_empleados', function (Blueprint $table) {
            $table->id(); // Llave primaria autoincremental 'id'

            $table->unsignedBigInteger('id_empleado');
            $table->foreign('id_empleado')
                  ->references('id_empleado')
                  ->on('empleados')
                  ->onDelete('cascade'); // Si se borra un empleado, se borran sus préstamos

            $table->date('fecha_solicitud'); // O fecha_otorgamiento
            $table->decimal('monto_total_prestamo', 10, 2); // Calculado: monto_pago_quincenal * plazo_quincenas
            $table->integer('plazo_quincenas'); // Ingresado por usuario
            $table->decimal('monto_pago_quincenal', 8, 2); // Ingresado por usuario

            $table->integer('quincenas_pagadas')->default(0);
            $table->decimal('saldo_pendiente', 10, 2); // Inicialmente igual a monto_total_prestamo

            $table->string('status_prestamo', 50)->default('Activo'); // Ej: Activo, Pagado, Cancelado
            $table->text('comentarios')->nullable();
            $table->timestamps(); // created_at y updated_at

            $table->index('id_empleado');
            $table->index('status_prestamo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos_empleados');
    }
};
