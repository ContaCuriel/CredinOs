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
        // ===> ESPECIFICA LA CONEXIÓN 'landlord' AQUÍ <===
        Schema::connection('landlord')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->unsignedBigInteger('id_rol')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // ===> ESPECIFICA LA CONEXIÓN 'landlord' AQUÍ <===
        Schema::connection('landlord')->create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ===> ESPECIFICA LA CONEXIÓN 'landlord' AQUÍ <===
        Schema::connection('landlord')->create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index(); // Asume que user_id se refiere a users en landlord
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ===> ESPECIFICA LA CONEXIÓN 'landlord' AQUÍ <===
        Schema::connection('landlord')->dropIfExists('users');
        Schema::connection('landlord')->dropIfExists('password_reset_tokens');
        Schema::connection('landlord')->dropIfExists('sessions');
    }
};