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
        // Asumiendo que tu tabla pivote se llama 'client_group'
        Schema::table('client_group', function (Blueprint $table) {
            $table->decimal('individual_amount', 10, 2)->nullable()->after('client_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_group', function (Blueprint $table) {
            $table->dropColumn('individual_amount');
        });
    }
};