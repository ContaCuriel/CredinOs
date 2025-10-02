<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // 1. Cambiar vencimiento_ine a solo el año (INTEGER)
            $table->integer('vencimiento_ine')->nullable()->change();

            // 2. Añadir nuevos campos
            $table->string('telefono_fijo')->nullable()->after('telefono_celular');
            $table->integer('anios_domicilio')->unsigned()->nullable()->after('fecha_comprobante_domicilio');
            $table->string('tipo_vivienda')->nullable()->after('anios_domicilio');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Revertir el cambio de vencimiento_ine a DATE
            $table->date('vencimiento_ine')->nullable()->change();

            // Eliminar las columnas añadidas
            $table->dropColumn(['telefono_fijo', 'anios_domicilio', 'tipo_vivienda']);
        });
    }
};