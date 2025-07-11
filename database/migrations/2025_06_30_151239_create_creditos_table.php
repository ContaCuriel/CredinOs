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
    Schema::create('creditos', function (Blueprint $table) {
        $table->id('id_credito');

        // --- Relación Polimórfica ---
        // Estas dos columnas nos permitirán asociar un crédito
        // tanto a un Cliente como a un Grupo.
        $table->unsignedBigInteger('loanable_id');
        $table->string('loanable_type');

        // --- Datos Generales del Crédito ---
        $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal');
        $table->foreignId('id_asesor')->constrained('users', 'id');
        $table->decimal('monto_solicitado', 10, 2);
        $table->decimal('monto_autorizado', 10, 2)->nullable();
        $table->integer('plazo'); // En semanas
        $table->string('frecuencia_pago')->default('Semanal');
        $table->decimal('tasa_interes', 5, 2);
        $table->date('fecha_solicitud');
        $table->date('fecha_desembolso')->nullable();
        $table->enum('status', ['Solicitud', 'Aprobado', 'Rechazado', 'Activo', 'Pagado', 'En Mora'])->default('Solicitud');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creditos');
    }
};
