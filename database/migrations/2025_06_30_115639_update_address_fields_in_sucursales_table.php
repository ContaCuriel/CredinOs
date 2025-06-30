<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            // Añadimos las nuevas columnas después de 'nombre_sucursal'
            $table->string('calle')->nullable()->after('nombre_sucursal');
            $table->string('numero')->nullable()->after('calle');
            $table->string('colonia')->nullable()->after('numero');
            $table->string('municipio')->nullable()->after('colonia');
            $table->string('estado')->nullable()->after('municipio');
            $table->string('codigo_postal')->nullable()->after('estado');

            // Hacemos la columna antigua opcional para poder migrar los datos sin problemas
            $table->string('direccion_sucursal')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sucursales', function (Blueprint $table) {
            // Esto permite revertir la migración si algo sale mal
            $table->dropColumn([
                'calle',
                'numero',
                'colonia',
                'municipio',
                'estado',
                'codigo_postal',
            ]);
        });
    }
};