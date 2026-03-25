<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant; // Aseguramos usar tu modelo Tenant

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentTenant = Tenant::current();

        if (!$currentTenant) {
            $this->command->error("No se pudo detectar el Tenant actual.");
            return;
        }

        // 1. REGLA DE ORO EXACTA DE TU DOCUMENTACIÓN
        $dbName = $currentTenant->db_database;
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        if (empty($schema)) {
            $this->command->error("El esquema evaluado está vacío.");
            return;
        }

        // 2. Limpiamos la tabla apuntando directamente al esquema en Postgres
        DB::statement("TRUNCATE \"$schema\".accounts CASCADE;");

        $accounts = [
            // Cuentas de Mayor
            ['name' => 'Activo', 'code' => '100', 'type' => 'activo', 'parent_id' => null],
            ['name' => 'Pasivo', 'code' => '200', 'type' => 'pasivo', 'parent_id' => null],
            ['name' => 'Capital Contable', 'code' => '300', 'type' => 'capital', 'parent_id' => null],
            ['name' => 'Ingresos', 'code' => '400', 'type' => 'ingresos', 'parent_id' => null],
            ['name' => 'Costos', 'code' => '500', 'type' => 'costos', 'parent_id' => null],
            ['name' => 'Gastos de Operación', 'code' => '600', 'type' => 'gastos', 'parent_id' => null],
            ['name' => 'Gastos y Productos Financieros', 'code' => '800', 'type' => 'gastos', 'parent_id' => null],
            
            // Activo a corto plazo
            ['name' => 'Activo a corto plazo', 'code' => '100.01', 'type' => 'activo', 'parent_id' => '100'],
            ['name' => 'Caja', 'code' => '101.01', 'type' => 'activo', 'parent_id' => '100.01'],
            ['name' => 'Bancos', 'code' => '102.01', 'type' => 'activo', 'parent_id' => '100.01'],
            ['name' => 'Clientes', 'code' => '105.01', 'type' => 'activo', 'parent_id' => '100.01'],
            ['name' => 'IVA Acreditable', 'code' => '118.01', 'type' => 'activo', 'parent_id' => '100.01'],
            
            // Pasivo a corto plazo
            ['name' => 'Pasivo a corto plazo', 'code' => '200.01', 'type' => 'pasivo', 'parent_id' => '200'],
            ['name' => 'Proveedores', 'code' => '201.01', 'type' => 'pasivo', 'parent_id' => '200.01'],
            ['name' => 'Impuestos por pagar', 'code' => '208.01', 'type' => 'pasivo', 'parent_id' => '200.01'],
            
            // Ingresos
            ['name' => 'Ingresos', 'code' => '401', 'type' => 'ingresos', 'parent_id' => '400'],
            ['name' => 'Ingresos por intereses (actividad propia)', 'code' => '401.32', 'type' => 'ingresos', 'parent_id' => '401'],
            ['name' => 'Recuperación de cartera castigada', 'code' => '401.38', 'type' => 'ingresos', 'parent_id' => '401'],
            
            // Gastos Generales
            ['name' => 'Gastos generales', 'code' => '601', 'type' => 'gastos', 'parent_id' => '600'],
            ['name' => 'Sueldos y salarios', 'code' => '601.01', 'type' => 'gastos', 'parent_id' => '601'],
            ['name' => 'Comisiones a personal', 'code' => '601.03', 'type' => 'gastos', 'parent_id' => '601'],
            ['name' => 'Previsión social', 'code' => '601.09', 'type' => 'gastos', 'parent_id' => '601'],
            ['name' => 'Castigos (gastos no deducibles)', 'code' => '601.10', 'type' => 'gastos', 'parent_id' => '601'],
            ['name' => 'Arrendamiento', 'code' => '601.21', 'type' => 'gastos', 'parent_id' => '601'],
            ['name' => 'Telecomunicaciones', 'code' => '601.25', 'type' => 'gastos', 'parent_id' => '601'],
            
            // Otros Gastos
            ['name' => 'Gastos de venta', 'code' => '701', 'type' => 'gastos', 'parent_id' => '600'],
            ['name' => 'Gastos financieros', 'code' => '803', 'type' => 'gastos', 'parent_id' => '800'],
            ['name' => 'Comisiones bancarias', 'code' => '803.01', 'type' => 'gastos', 'parent_id' => '803'],
        ];

        // 3. Insertamos usando la ruta completa "esquema.tabla"
        foreach ($accounts as $acc) {
            $parentId = null;
            if ($acc['parent_id'] !== null) {
                // Buscamos usando el esquema detectado por la regla de oro
                $parentRow = DB::table("$schema.accounts")->where('code', $acc['parent_id'])->first();
                $parentId = $parentRow ? $parentRow->id : null;
            }

            DB::table("$schema.accounts")->insert([
                'name' => $acc['name'],
                'code' => $acc['code'],
                'type' => $acc['type'],
                'parent_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("El catálogo de cuentas se insertó correctamente en el esquema: $schema");
    }
}