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
        $table->dropColumn('cartas_recomendacion_texto');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('contratos', function (Blueprint $table) {
        $table->text('cartas_recomendacion_texto')->nullable()->comment('Texto para cartas de recomendación');
    });
}
};
