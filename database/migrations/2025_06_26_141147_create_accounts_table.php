<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre de la cuenta (Ej: "Caja y Bancos")
            $table->string('code'); // Código contable (Ej: "1101", "5105")
            
            // Tipo de cuenta: fundamental para los reportes financieros
            $table->enum('type', ['activo', 'pasivo', 'capital', 'ingresos', 'costos', 'gastos']);
            
            $table->text('description')->nullable(); // Descripción opcional
            
            // Para la estructura de árbol (jerarquía)
            $table->foreignId('parent_id')->nullable()->constrained('accounts')->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};