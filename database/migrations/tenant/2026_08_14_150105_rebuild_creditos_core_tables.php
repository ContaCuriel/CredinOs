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

        // 1. LA DEMOLICIÓN: Borramos todo rastro de las tablas mal formadas
        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".credito_cuentas_desembolso CASCADE");
        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".credito_clientes CASCADE");
        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".creditos CASCADE");

        // 2. CONSTRUIMOS EL FERRARI (Tabla principal de créditos con Sucursal incluida)
        DB::connection('tenant')->statement("CREATE TABLE \"$schema\".creditos (
            id SERIAL PRIMARY KEY,
            folio VARCHAR(50) UNIQUE NOT NULL,
            nombre_credito VARCHAR(255) NULL,
            sucursal_id BIGINT NULL,
            cliente_id BIGINT NULL,
            grupo_id BIGINT NULL,
            producto_id BIGINT NOT NULL,
            monto_solicitado DECIMAL(12,2) NOT NULL,
            plazo_solicitado INTEGER NOT NULL,
            monto_aprobado DECIMAL(12,2) NULL,
            plazo_aprobado INTEGER NULL,
            tasa_interes_aplicada DECIMAL(8,4) NOT NULL,
            comision_apertura_aplicada DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            estatus VARCHAR(50) DEFAULT 'solicitado',
            fecha_solicitud DATE NOT NULL,
            fecha_aprobacion DATE NULL,
            fecha_desembolso DATE NULL,
            fecha_primer_pago DATE NULL,
            fecha_vencimiento DATE NULL,
            asesor_id BIGINT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // 3. CONSTRUIMOS LOS INTEGRANTES
        DB::connection('tenant')->statement("CREATE TABLE \"$schema\".credito_clientes (
            id SERIAL PRIMARY KEY,
            credito_id BIGINT NOT NULL,
            cliente_id BIGINT NOT NULL,
            es_lider BOOLEAN DEFAULT FALSE,
            monto_individual DECIMAL(12,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_cc_credito FOREIGN KEY (credito_id) REFERENCES \"$schema\".creditos (id) ON DELETE CASCADE,
            CONSTRAINT fk_cc_cliente FOREIGN KEY (cliente_id) REFERENCES \"$schema\".clientes (id_cliente) ON DELETE CASCADE
        )");

        // 4. CONSTRUIMOS LAS CUENTAS DE DESEMBOLSO
        DB::connection('tenant')->statement("CREATE TABLE \"$schema\".credito_cuentas_desembolso (
            id SERIAL PRIMARY KEY,
            credito_id BIGINT NOT NULL,
            banco VARCHAR(100) NOT NULL,
            titular VARCHAR(255) NULL,
            numero_cuenta VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_ccd_credito FOREIGN KEY (credito_id) REFERENCES \"$schema\".creditos (id) ON DELETE CASCADE
        )");
    }

    public function down(): void
    {
        // En caso de querer echar para atrás
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".credito_cuentas_desembolso CASCADE");
        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".credito_clientes CASCADE");
        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".creditos CASCADE");
    }
};