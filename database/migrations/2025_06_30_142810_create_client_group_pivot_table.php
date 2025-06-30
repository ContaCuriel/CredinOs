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
    Schema::create('client_group', function (Blueprint $table) {
        $table->foreignId('client_id')->constrained('clientes', 'id_cliente')->onDelete('cascade');
        $table->foreignId('group_id')->constrained('groups', 'id_group')->onDelete('cascade');
        // Clave primaria compuesta para evitar que un cliente esté dos veces en el mismo grupo
        $table->primary(['client_id', 'group_id']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_group_pivot');
    }
};
