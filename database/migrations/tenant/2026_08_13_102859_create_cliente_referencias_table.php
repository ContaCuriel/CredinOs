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

        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".cliente_referencias (
            id SERIAL PRIMARY KEY,
            cliente_id BIGINT NOT NULL,
            nombre_referencia VARCHAR(255) NOT NULL,
            parentesco VARCHAR(100) NOT NULL,
            telefono VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_cliente_referencia FOREIGN KEY (cliente_id) REFERENCES \"$schema\".clientes (id_cliente) ON DELETE CASCADE
        )";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".cliente_referencias";

        DB::connection('tenant')->statement($sql);
    }
};