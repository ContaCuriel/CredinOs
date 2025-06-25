<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Creamos la columna. Es importante que sea del mismo tipo que la llave primaria de sucursales.
            // 'unsignedBigInteger' es el estándar para llaves foráneas en Laravel.
            $table->unsignedBigInteger('id_sucursal')->nullable();

            // Creamos la segunda columna
            $table->boolean('ver_todas_sucursales')->default(false);

            // Ahora, definimos el "enlace" (llave foránea) manualmente
            $table->foreign('id_sucursal')          // La columna en esta tabla (users)
                  ->references('id_sucursal')  // La columna a la que apunta en la otra tabla
                  ->on('sucursales')             // El nombre de la otra tabla
                  ->onDelete('set null');       // Si se borra una sucursal, el campo se pone nulo
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_sucursal']);
            $table->dropColumn(['id_sucursal', 'ver_todas_sucursales']);
        });
    }
};