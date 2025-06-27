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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();

            // Relación con el encabezado de la póliza
            $table->foreignId('journal_id')->constrained('journals')->onDelete('cascade');
            
            // Relación con el catálogo de cuentas
            $table->foreignId('account_id')->constrained('accounts')->onDelete('restrict');

            // Columnas para el DEBE (cargo) y el HABER (abono)
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            
            $table->timestamps();

            // La línea que causaba el error ($table->check(...)) ha sido eliminada.
            // La lógica de la aplicación se encargará de que solo uno de los dos campos tenga valor.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
