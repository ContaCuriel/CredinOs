<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('clientes', function (Blueprint $table) {
            // Hacemos la columna opcional (nullable)
            $table->unsignedBigInteger('id_sucursal')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('clientes', function (Blueprint $table) {
            // La revierte a obligatoria si es necesario
            $table->unsignedBigInteger('id_sucursal')->nullable(false)->change();
        });
    }
};