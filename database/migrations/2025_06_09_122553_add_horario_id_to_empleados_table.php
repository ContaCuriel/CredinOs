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
            if (!Schema::hasColumn('empleados', 'id_horario')) {
                $table->unsignedBigInteger('id_horario')->nullable()->after('id_sucursal');

                $table->foreign('id_horario')
                      ->references('id_horario')
                      ->on('horarios')
                      ->onDelete('set null'); // Si se borra un horario, el empleado se queda sin horario asignado
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'id_horario')) {
                $table->dropForeign(['id_horario']);
                $table->dropColumn('id_horario');
            }
        });
    }
};