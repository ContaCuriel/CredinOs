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
    Schema::create('payment_installments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('credito_id')->constrained('creditos', 'id_credito')->onDelete('cascade');
        $table->integer('numero_pago');
        $table->decimal('monto_pago', 10, 2);
        $table->decimal('monto_capital', 10, 2);
        $table->decimal('monto_interes', 10, 2);
        $table->date('fecha_vencimiento');
        $table->enum('status', ['Pendiente', 'Pagado', 'Atrasado'])->default('Pendiente');
        $table->date('fecha_pago')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};
