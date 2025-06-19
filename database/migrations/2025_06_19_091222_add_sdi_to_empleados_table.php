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
            // Usaremos decimal para valores monetarios. Lo ponemos después de id_patron_imss.
            $table->decimal('sdi', 10, 2)->nullable()->comment('Salario Diario Integrado para el IMSS')->after('id_patron_imss');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('sdi');
        });
    }
};
