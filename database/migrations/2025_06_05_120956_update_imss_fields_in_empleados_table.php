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
        Schema::table('empleados', function (Blueprint $table) {
            // Modificar la columna 'imss' existente a 'estado_imss' o añadirla si no existe
            // Es más seguro eliminar la columna booleana 'imss' y añadir 'estado_imss'
            if (Schema::hasColumn('empleados', 'imss')) {
                $table->dropColumn('imss');
            }
            if (!Schema::hasColumn('empleados', 'estado_imss')) {
                $table->string('estado_imss', 20)->nullable()->after('nss')->comment('Estado en el IMSS: Alta, Baja, No Registrado');
            } else {
                $table->string('estado_imss', 20)->nullable()->change();
            }

            // Asegurar que fecha_alta_imss sea nullable
            if (Schema::hasColumn('empleados', 'fecha_alta_imss')) {
                $table->date('fecha_alta_imss')->nullable()->change();
            } elseif(!Schema::hasColumn('empleados', 'fecha_alta_imss')) {
                $table->date('fecha_alta_imss')->nullable()->after('estado_imss');
            }

            // Añadir nuevas columnas
            if (!Schema::hasColumn('empleados', 'fecha_baja_imss')) {
                $table->date('fecha_baja_imss')->nullable()->after('fecha_alta_imss');
            }
            if (!Schema::hasColumn('empleados', 'id_patron_imss')) {
                $table->unsignedBigInteger('id_patron_imss')->nullable()->after('fecha_baja_imss');
                $table->foreign('id_patron_imss')
                      ->references('id_patron')
                      ->on('patrones')
                      ->onDelete('set null'); // Si se borra el patrón, el id_patron_imss del empleado se vuelve null
            }
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'id_patron_imss')) {
                $table->dropForeign(['id_patron_imss']);
                $table->dropColumn('id_patron_imss');
            }
            if (Schema::hasColumn('empleados', 'fecha_baja_imss')) {
                $table->dropColumn('fecha_baja_imss');
            }
            // Revertir estado_imss a la columna 'imss' booleana podría ser complejo
            // dependiendo de los datos. Por simplicidad, solo la eliminamos.
            // Si necesitas una reversión exacta, tendrías que definir cómo mapear los estados de vuelta.
            if (Schema::hasColumn('empleados', 'estado_imss')) {
                $table->dropColumn('estado_imss');
            }
            // Si quieres restaurar la columna 'imss' booleana:
            // if (!Schema::hasColumn('empleados', 'imss')) {
            //     $table->boolean('imss')->default(false)->after('nss');
            // }
        });
    }
};
