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

        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".productos_credito (
            id SERIAL PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            tipo_credito VARCHAR(50) NOT NULL CHECK (tipo_credito IN ('individual', 'grupal')),
            frecuencia_pago VARCHAR(50) NOT NULL CHECK (frecuencia_pago IN ('diario', 'semanal', 'catorcenal', 'quincenal', 'mensual', 'pago_al_final')),
            tasa_interes DECIMAL(8,4) NOT NULL,
            tipo_tasa VARCHAR(50) NOT NULL CHECK (tipo_tasa IN ('global', 'saldo_insoluto')),
            
            plazo_minimo INTEGER NOT NULL,
            plazo_maximo INTEGER NOT NULL,
            monto_minimo DECIMAL(12,2) NOT NULL,
            monto_maximo DECIMAL(12,2) NOT NULL,
            
            -- ================= REGLAS DE CASTIGOS ================= --
            hora_maxima_pago TIME NULL,
            
            -- MULTA (Ej. 500 pesos fijos si se pasa de las 10:00 AM)
            multa_trigger VARCHAR(50) DEFAULT 'no_aplica' CHECK (multa_trigger IN ('despues_de_hora', 'despues_de_dia', 'no_aplica')),
            multa_valor DECIMAL(12,2) DEFAULT 0.00,
            multa_calculo VARCHAR(50) DEFAULT 'fijo' CHECK (multa_calculo IN ('fijo', 'porcentaje_pago', 'porcentaje_saldo', 'porcentaje_credito')),
            
            -- MORA (Ej. 10% del crédito total si se pasa al día siguiente)
            mora_trigger VARCHAR(50) DEFAULT 'no_aplica' CHECK (mora_trigger IN ('despues_de_hora', 'despues_de_dia', 'no_aplica')),
            mora_valor DECIMAL(12,2) DEFAULT 0.00,
            mora_calculo VARCHAR(50) DEFAULT 'fijo' CHECK (mora_calculo IN ('fijo', 'porcentaje_pago', 'porcentaje_saldo', 'porcentaje_credito')),
            
            -- POLÍTICA DE ACUMULACIÓN (¿Qué pasa si rompe ambas reglas?)
            politica_acumulacion VARCHAR(50) DEFAULT 'acumular' CHECK (politica_acumulacion IN ('acumular', 'solo_mayor', 'reemplazar')),
            -- ====================================================== --

            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".productos_credito";

        DB::connection('tenant')->statement($sql);
    }
};