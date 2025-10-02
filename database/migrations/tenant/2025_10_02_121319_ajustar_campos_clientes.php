<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Usamos una sentencia directa para la conversión en PostgreSQL
        DB::statement('ALTER TABLE clientes ALTER COLUMN vencimiento_ine TYPE INTEGER USING EXTRACT(YEAR FROM vencimiento_ine)');

        // El resto de los cambios se quedan igual
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('telefono_fijo')->nullable()->after('telefono_celular');
            $table->integer('anios_domicilio')->unsigned()->nullable()->after('fecha_comprobante_domicilio');
            $table->string('tipo_vivienda')->nullable()->after('anios_domicilio');
        });
    }

    public function down(): void
    {
        // También usamos una sentencia directa para revertir el cambio
        DB::statement("ALTER TABLE clientes ALTER COLUMN vencimiento_ine TYPE DATE USING make_date(vencimiento_ine, 12, 31)");
        
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn(['telefono_fijo', 'anios_domicilio', 'tipo_vivienda']);
        });
    }
};