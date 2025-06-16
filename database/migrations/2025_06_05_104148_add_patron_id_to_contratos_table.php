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
        Schema::table('contratos', function (Blueprint $table) {
            // Añadimos la nueva columna para la llave foránea
            // La hacemos nullable temporalmente si ya tienes datos en 'contratos'
            // y la colocamos después de id_empleado por orden.
            if (!Schema::hasColumn('contratos', 'id_patron')) {
                $table->unsignedBigInteger('id_patron')->nullable()->after('id_empleado');

                // Definimos la llave foránea
                // Asumimos que tu tabla patrones usa 'id_patron' como PK
                $table->foreign('id_patron')
                      ->references('id_patron')
                      ->on('patrones')
                      ->onDelete('restrict'); // O 'set null' si prefieres que el contrato no se borre si se borra el patrón
            }

            // Opcional: Si quieres mantener patron_tipo, puedes dejarlo.
            // Si no, y si 'id_patron' ahora será obligatorio, puedes considerar eliminar 'patron_tipo'
            // o hacerlo nullable si 'id_patron' también puede serlo inicialmente.
            // Por ahora, lo dejamos.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contratos', function (Blueprint $table) {
            if (Schema::hasColumn('contratos', 'id_patron')) {
                // Para eliminar una llave foránea, necesitas saber su nombre convencional
                // Laravel lo nombra como: contratos_id_patron_foreign
                // O puedes pasar un array de columnas a dropForeign
                $table->dropForeign(['id_patron']);
                $table->dropColumn('id_patron');
            }
        });
    }
};
