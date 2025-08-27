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
        Schema::table('creditos', function (Blueprint $table) {
            // Verificamos si la columna NO existe antes de añadirla
            if (!Schema::hasColumn('creditos', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('id_credito');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            // Verificamos si la columna SÍ existe antes de intentar borrarla
            if (Schema::hasColumn('creditos', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
        });
    }
};