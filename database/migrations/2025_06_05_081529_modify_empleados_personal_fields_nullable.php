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
            // Change columns to be nullable
            // Important: We assume unique constraints should remain if they exist
            // but allow multiple nulls if the DB engine supports it (MySQL does).
            // If a column was previously not nullable and unique, making it nullable
            // while keeping unique means multiple rows can be NULL, but any non-NULL value must still be unique.
            if (Schema::hasColumn('empleados', 'telefono')) {
                $table->string('telefono', 15)->nullable()->change();
            }
            if (Schema::hasColumn('empleados', 'nss')) {
                // For unique columns that become nullable, first drop unique, change, then re-add unique if needed
                // However, MySQL allows multiple NULLs in a unique column. So just ->nullable()->change() might be enough
                // if the unique constraint already exists and permits this.
                // For safety if it was NOT NULL UNIQUE, the full change might be:
                // $table->dropUnique(['nss']); // If it was unique and you need to modify its definition for nulls
                $table->string('nss', 11)->nullable()->change();
                // $table->unique('nss'); // Re-add unique constraint if dropped
            }
            if (Schema::hasColumn('empleados', 'rfc')) {
                $table->string('rfc', 13)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            // Revert to not nullable - CAUTION: this will fail if there are NULL values.
            // Only do this if you are sure how to handle existing NULLs or have a backup.
            // For simplicity, we might just make them nullable and not revert strictly.
            // Or ensure they are filled before rollback.
            if (Schema::hasColumn('empleados', 'telefono')) {
                $table->string('telefono', 15)->nullable(false)->change(); 
            }
            if (Schema::hasColumn('empleados', 'nss')) {
                $table->string('nss', 11)->nullable(false)->change();
            }
            if (Schema::hasColumn('empleados', 'rfc')) {
                $table->string('rfc', 13)->nullable(false)->change();
            }
        });
    }
};
