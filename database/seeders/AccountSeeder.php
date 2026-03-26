<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            $this->command->error("No se pudo detectar el Tenant actual.");
            return;
        }

        // 1. Nos conectamos usando la misma lógica maestra que usas en tus migraciones
        Config::set('database.connections.tenant.host', $tenant->db_host);
        Config::set('database.connections.tenant.database', $tenant->db_database);
        Config::set('database.connections.tenant.username', $tenant->db_username);
        Config::set('database.connections.tenant.password', $tenant->db_password);
        Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432);

        DB::purge('tenant');
        DB::reconnect('tenant');

        $db = DB::connection('tenant');

        // 2. Definimos las cuentas con una clave 'parent_code' para no perdernos
        $accounts = [
            // Cuentas de Mayor
            ['name' => 'Activo', 'code' => '100', 'type' => 'activo', 'parent_code' => null],
            ['name' => 'Pasivo', 'code' => '200', 'type' => 'pasivo', 'parent_code' => null],
            ['name' => 'Capital Contable', 'code' => '300', 'type' => 'capital', 'parent_code' => null],
            ['name' => 'Ingresos', 'code' => '400', 'type' => 'ingresos', 'parent_code' => null],
            ['name' => 'Costos', 'code' => '500', 'type' => 'costos', 'parent_code' => null],
            ['name' => 'Gastos de Operación', 'code' => '600', 'type' => 'gastos', 'parent_code' => null],
            ['name' => 'Gastos y Productos Financieros', 'code' => '800', 'type' => 'gastos', 'parent_code' => null],
            
            // Activo a corto plazo
            ['name' => 'Activo a corto plazo', 'code' => '100.01', 'type' => 'activo', 'parent_code' => '100'],
            ['name' => 'Caja', 'code' => '101.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Bancos', 'code' => '102.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Clientes', 'code' => '105.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'IVA Acreditable', 'code' => '118.01', 'type' => 'activo', 'parent_code' => '100.01'],
            
            // Pasivo a corto plazo
            ['name' => 'Pasivo a corto plazo', 'code' => '200.01', 'type' => 'pasivo', 'parent_code' => '200'],
            ['name' => 'Proveedores', 'code' => '201.01', 'type' => 'pasivo', 'parent_code' => '200.01'],
            ['name' => 'Impuestos por pagar', 'code' => '208.01', 'type' => 'pasivo', 'parent_code' => '200.01'],
            
            // Ingresos
            ['name' => 'Ingresos', 'code' => '401', 'type' => 'ingresos', 'parent_code' => '400'],
            ['name' => 'Ingresos por intereses (actividad propia)', 'code' => '401.32', 'type' => 'ingresos', 'parent_code' => '401'],
            ['name' => 'Recuperación de cartera castigada', 'code' => '401.38', 'type' => 'ingresos', 'parent_code' => '401'],
            
            // Gastos Generales
            ['name' => 'Gastos generales', 'code' => '601', 'type' => 'gastos', 'parent_code' => '600'],
            ['name' => 'Sueldos y salarios', 'code' => '601.01', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Comisiones a personal', 'code' => '601.03', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Previsión social', 'code' => '601.09', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Castigos (gastos no deducibles)', 'code' => '601.10', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Arrendamiento', 'code' => '601.21', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Telecomunicaciones', 'code' => '601.25', 'type' => 'gastos', 'parent_code' => '601'],
            
            // Otros Gastos
            ['name' => 'Gastos de venta', 'code' => '701', 'type' => 'gastos', 'parent_code' => '600'],
            ['name' => 'Gastos financieros', 'code' => '803', 'type' => 'gastos', 'parent_code' => '800'],
            ['name' => 'Comisiones bancarias', 'code' => '803.01', 'type' => 'gastos', 'parent_code' => '803'],
        ];

        // 3. ¡SIN TRUNCATE! Insertamos o Actualizamos inteligentemente línea por línea
        foreach ($accounts as $acc) {
            $parentId = null;
            if ($acc['parent_code'] !== null) {
                // Buscamos al padre recién insertado
                $parentRow = $db->table('accounts')->where('code', $acc['parent_code'])->first();
                $parentId = $parentRow ? $parentRow->id : null;
            }

            $exists = $db->table('accounts')->where('code', $acc['code'])->exists();

            if (!$exists) {
                $db->table('accounts')->insert([
                    'code' => $acc['code'],
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'parent_id' => $parentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $db->table('accounts')->where('code', $acc['code'])->update([
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'parent_id' => $parentId,
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info("¡Catálogo de cuentas insertado/actualizado sin borrar nada en: {$tenant->db_database}!");
    }
}