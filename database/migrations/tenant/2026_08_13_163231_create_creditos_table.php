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

        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".creditos (
            id SERIAL PRIMARY KEY,
            folio VARCHAR(50) UNIQUE NOT NULL,
            nombre_credito VARCHAR(255) NULL, 
            
            -- Relaciones
            cliente_id BIGINT NULL,
            grupo_id BIGINT NULL,
            producto_id BIGINT NOT NULL,
            
            -- Trazabilidad de montos y plazos
            monto_solicitado DECIMAL(12,2) NOT NULL,
            plazo_solicitado INTEGER NOT NULL,
            monto_aprobado DECIMAL(12,2) NULL,
            plazo_aprobado INTEGER NULL,
            
            -- Fotografía de condiciones
            tasa_interes_aplicada DECIMAL(8,4) NOT NULL,
            comision_apertura_aplicada DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            
            -- Ciclo de vida y fechas
            estatus VARCHAR(50) DEFAULT 'solicitado' CHECK (estatus IN ('solicitado', 'aprobado', 'rechazado', 'desembolsado', 'liquidado', 'vencido', 'castigado')),
            fecha_solicitud DATE NOT NULL,
            fecha_aprobacion DATE NULL,
            fecha_desembolso DATE NULL,
            fecha_primer_pago DATE NULL,
            fecha_vencimiento DATE NULL,
            
            -- Auditoría
            asesor_id BIGINT NULL, 
            
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            
            -- Llaves foráneas
            CONSTRAINT fk_credito_cliente FOREIGN KEY (cliente_id) REFERENCES \"$schema\".clientes (id_cliente) ON DELETE SET NULL,
            CONSTRAINT fk_credito_grupo FOREIGN KEY (grupo_id) REFERENCES \"$schema\".grupos (id) ON DELETE SET NULL,
            CONSTRAINT fk_credito_producto FOREIGN KEY (producto_id) REFERENCES \"$schema\".productos_credito (id) ON DELETE RESTRICT
        )";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".creditos";
        DB::connection('tenant')->statement($sql);
    }
};