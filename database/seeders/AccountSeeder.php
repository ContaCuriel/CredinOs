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

        $accounts = [
            // ================= CUENTAS DE MAYOR (NIVEL 1) =================
            ['name' => 'Activo', 'code' => '100', 'type' => 'activo', 'parent_code' => null],
            ['name' => 'Pasivo', 'code' => '200', 'type' => 'pasivo', 'parent_code' => null],
            ['name' => 'Capital Contable', 'code' => '300', 'type' => 'capital', 'parent_code' => null],
            ['name' => 'Ingresos', 'code' => '400', 'type' => 'ingresos', 'parent_code' => null],
            ['name' => 'Costos', 'code' => '500', 'type' => 'costos', 'parent_code' => null],
            ['name' => 'Gastos de Operación', 'code' => '600', 'type' => 'gastos', 'parent_code' => null],
            ['name' => 'Gastos y Productos Financieros', 'code' => '800', 'type' => 'gastos', 'parent_code' => null],
            
            // ================= 100 - ACTIVOS =================
            ['name' => 'Activo a corto plazo', 'code' => '100.01', 'type' => 'activo', 'parent_code' => '100'],
            ['name' => 'Caja', 'code' => '101.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Bancos', 'code' => '102.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Inversiones a corto plazo', 'code' => '103.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Clientes', 'code' => '105.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Deudores diversos', 'code' => '107.01', 'type' => 'activo', 'parent_code' => '100.01'], // Para prestamos a empleados, etc.
            ['name' => 'IVA Acreditable (Pagado)', 'code' => '118.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'IVA Pendiente de Acreditar', 'code' => '118.02', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Impuestos a favor (ISR/IVA)', 'code' => '119.01', 'type' => 'activo', 'parent_code' => '100.01'],
            ['name' => 'Anticipo a proveedores', 'code' => '120.01', 'type' => 'activo', 'parent_code' => '100.01'],
            
            ['name' => 'Activo a largo plazo (Fijo)', 'code' => '100.02', 'type' => 'activo', 'parent_code' => '100'],
            ['name' => 'Mobiliario y equipo de oficina', 'code' => '152.01', 'type' => 'activo', 'parent_code' => '100.02'],
            ['name' => 'Equipo de cómputo', 'code' => '154.01', 'type' => 'activo', 'parent_code' => '100.02'],
            ['name' => 'Depreciación acumulada', 'code' => '158.01', 'type' => 'activo', 'parent_code' => '100.02'],

            // ================= 200 - PASIVOS =================
            ['name' => 'Pasivo a corto plazo', 'code' => '200.01', 'type' => 'pasivo', 'parent_code' => '200'],
            ['name' => 'Proveedores', 'code' => '201.01', 'type' => 'pasivo', 'parent_code' => '200.01'],
            ['name' => 'Acreedores diversos', 'code' => '205.01', 'type' => 'pasivo', 'parent_code' => '200.01'], // Deudas varias que no son de giro
            ['name' => 'Anticipo de clientes', 'code' => '206.01', 'type' => 'pasivo', 'parent_code' => '200.01'],
            ['name' => 'Impuestos y cuotas por pagar', 'code' => '208.01', 'type' => 'pasivo', 'parent_code' => '200.01'], // IMSS, ISR Retenido, etc.
            ['name' => 'IVA Trasladado (Cobrado)', 'code' => '209.01', 'type' => 'pasivo', 'parent_code' => '200.01'],
            ['name' => 'IVA Pendiente de Trasladar', 'code' => '209.02', 'type' => 'pasivo', 'parent_code' => '200.01'],

            // ================= 300 - CAPITAL =================
            ['name' => 'Capital Social', 'code' => '301.01', 'type' => 'capital', 'parent_code' => '300'],
            ['name' => 'Resultados de ejercicios anteriores', 'code' => '304.01', 'type' => 'capital', 'parent_code' => '300'],
            ['name' => 'Resultado del ejercicio', 'code' => '305.01', 'type' => 'capital', 'parent_code' => '300'],

            // ================= 400 - INGRESOS =================
            ['name' => 'Ingresos', 'code' => '401', 'type' => 'ingresos', 'parent_code' => '400'],
            ['name' => 'Ingresos por intereses (actividad propia)', 'code' => '401.32', 'type' => 'ingresos', 'parent_code' => '401'],
            ['name' => 'Recuperación de cartera castigada', 'code' => '401.38', 'type' => 'ingresos', 'parent_code' => '401'],
            ['name' => 'Comisiones cobradas', 'code' => '401.40', 'type' => 'ingresos', 'parent_code' => '401'], // Por apertura, moratorios, etc.
            ['name' => 'Otros ingresos', 'code' => '401.99', 'type' => 'ingresos', 'parent_code' => '401'],
            
            // ================= 600 - GASTOS =================
            ['name' => 'Gastos generales', 'code' => '601', 'type' => 'gastos', 'parent_code' => '600'],
            ['name' => 'Sueldos y salarios', 'code' => '601.01', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Cuotas obrero patronales (IMSS/Infonavit)', 'code' => '601.02', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Comisiones a personal', 'code' => '601.03', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Previsión social', 'code' => '601.09', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Castigos (gastos no deducibles)', 'code' => '601.10', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Honorarios profesionales y legales', 'code' => '601.12', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Impuestos y derechos', 'code' => '601.15', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Papelería y útiles', 'code' => '601.17', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Mantenimiento de oficina', 'code' => '601.20', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Arrendamiento', 'code' => '601.21', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Pago de servicios (Luz, Agua, etc.)', 'code' => '601.22', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Telecomunicaciones (Teléfono, Internet)', 'code' => '601.25', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Viáticos y transporte', 'code' => '601.28', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Gastos de representación', 'code' => '601.29', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Capacitación', 'code' => '601.31', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Limpieza y aseo', 'code' => '601.32', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Publicidad y mercadotecnia', 'code' => '601.33', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Seguros y fianzas', 'code' => '601.40', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Combustibles y lubricantes', 'code' => '601.45', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Reembolsos a empleados', 'code' => '601.84', 'type' => 'gastos', 'parent_code' => '601'],
            ['name' => 'Otros gastos generales', 'code' => '601.99', 'type' => 'gastos', 'parent_code' => '601'],
            
            // ================= 700 / 800 - OTROS GASTOS Y FINANCIEROS =================
            ['name' => 'Gastos de venta', 'code' => '701', 'type' => 'gastos', 'parent_code' => '600'],
            ['name' => 'Gastos financieros', 'code' => '803', 'type' => 'gastos', 'parent_code' => '800'],
            ['name' => 'Comisiones bancarias', 'code' => '803.01', 'type' => 'gastos', 'parent_code' => '803'],
            ['name' => 'Intereses pagados', 'code' => '803.02', 'type' => 'gastos', 'parent_code' => '803'],
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