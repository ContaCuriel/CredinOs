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
            // Campos para la cuenta de desembolso del cliente
            $table->string('disbursement_bank', 100)->nullable()->after('status');
            $table->string('disbursement_clabe', 18)->nullable()->after('disbursement_bank');
            $table->string('disbursement_account_number', 20)->nullable()->after('disbursement_clabe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creditos', function (Blueprint $table) {
            $table->dropColumn([
                'disbursement_bank',
                'disbursement_clabe',
                'disbursement_account_number'
            ]);
        });
    }
};