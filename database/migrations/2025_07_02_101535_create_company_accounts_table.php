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
        Schema::create('company_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name', 150);
            $table->string('account_number', 50)->unique();
            $table->string('bank_name', 100);
            $table->string('beneficiary_name', 200);
            $table->string('clabe', 18)->unique()->nullable();
            
            // Para identificar la cuenta en reportes y conciliación
            $table->string('reference_code', 50)->unique()->nullable(); 

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_accounts');
    }
};