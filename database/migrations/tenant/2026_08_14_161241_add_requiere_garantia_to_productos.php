<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".productos_credito ADD COLUMN IF NOT EXISTS requiere_garantia BOOLEAN DEFAULT FALSE;");
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".productos_credito DROP COLUMN IF EXISTS requiere_garantia;");
    }
};