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

        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".credito_cuentas_desembolso (
            id SERIAL PRIMARY KEY,
            credito_id BIGINT NOT NULL,
            banco VARCHAR(100) NOT NULL,
            titular VARCHAR(255) NULL,
            numero_cuenta VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ccd_credito FOREIGN KEY (credito_id) REFERENCES \"$schema\".creditos (id) ON DELETE CASCADE
        )";
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".credito_cuentas_desembolso";
        DB::connection('tenant')->statement($sql);
    }
};