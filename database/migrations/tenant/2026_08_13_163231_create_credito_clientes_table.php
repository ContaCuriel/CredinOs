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

        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".credito_clientes (
            id SERIAL PRIMARY KEY,
            credito_id BIGINT NOT NULL,
            cliente_id BIGINT NOT NULL,
            es_lider BOOLEAN DEFAULT FALSE,
            monto_individual DECIMAL(12,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_cc_credito FOREIGN KEY (credito_id) REFERENCES \"$schema\".creditos (id_credito) ON DELETE CASCADE,
            CONSTRAINT fk_cc_cliente FOREIGN KEY (cliente_id) REFERENCES \"$schema\".clientes (id_cliente) ON DELETE CASCADE
        )";
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".credito_clientes";
        DB::connection('tenant')->statement($sql);
    }
};