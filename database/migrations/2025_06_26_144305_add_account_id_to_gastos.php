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
        // CAMBIO CLAVE: Aquí apuntamos a la tabla 'categorias'
        Schema::table('categorias', function (Blueprint $table) {
            $table->foreignId('account_id')
                  ->nullable()
                  ->after('nombre') // Asumo que tu tabla 'categorias' tiene una columna 'nombre'
                  ->constrained('accounts')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // CAMBIO CLAVE: También corregimos aquí
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};