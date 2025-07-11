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
    Schema::create('credito_cliente', function (Blueprint $table) {
        $table->id();

        // --- CORRECCIÓN AQUÍ ---
        // Le decimos explícitamente a qué tabla y columna debe conectarse.
        $table->foreignId('credito_id')->constrained(
            table: 'creditos', column: 'id_credito'
        );
        $table->foreignId('cliente_id')->constrained(
            table: 'clientes', column: 'id_cliente'
        );
        // --- FIN DE LA CORRECCIÓN ---

        $table->decimal('individual_amount', 10, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credito_cliente');
    }
};
