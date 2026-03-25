<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder implementa una versión completa del catálogo de cuentas del SAT.
     */
    public function run(): void
    {
        // Solución PostgreSQL: Limpia la tabla y las relaciones dependientes de forma segura
        DB::statement('TRUNCATE accounts CASCADE;');

        // Nivel 1: Cuentas de Mayor
        $activo = Account::create(['name' => 'Activo', 'code' => '100', 'type' => 'activo']);
        $pasivo = Account::create(['name' => 'Pasivo', 'code' => '200', 'type' => 'pasivo']);
        $capital = Account::create(['name' => 'Capital Contable', 'code' => '300', 'type' => 'capital']);
        $ingresos = Account::create(['name' => 'Ingresos', 'code' => '400', 'type' => 'ingresos']);
        $costos = Account::create(['name' => 'Costos', 'code' => '500', 'type' => 'costos']);
        $gastos = Account::create(['name' => 'Gastos de Operación', 'code' => '600', 'type' => 'gastos']);
        $gastosFinancieros = Account::create(['name' => 'Gastos y Productos Financieros', 'code' => '800', 'type' => 'gastos']);

        // --- Estructura de Activo ---
        $activoCorto = Account::create(['name' => 'Activo a corto plazo', 'code' => '100.01', 'type' => 'activo', 'parent_id' => $activo->id]);
        Account::create(['name' => 'Caja', 'code' => '101.01', 'type' => 'activo', 'parent_id' => $activoCorto->id]);
        Account::create(['name' => 'Bancos', 'code' => '102.01', 'type' => 'activo', 'parent_id' => $activoCorto->id]);
        Account::create(['name' => 'Clientes', 'code' => '105.01', 'type' => 'activo', 'parent_id' => $activoCorto->id]);
        Account::create(['name' => 'IVA Acreditable', 'code' => '118.01', 'type' => 'activo', 'parent_id' => $activoCorto->id]);
        
        // --- Estructura de Pasivo ---
        $pasivoCorto = Account::create(['name' => 'Pasivo a corto plazo', 'code' => '200.01', 'type' => 'pasivo', 'parent_id' => $pasivo->id]);
        Account::create(['name' => 'Proveedores', 'code' => '201.01', 'type' => 'pasivo', 'parent_id' => $pasivoCorto->id]);
        Account::create(['name' => 'Impuestos por pagar', 'code' => '208.01', 'type' => 'pasivo', 'parent_id' => $pasivoCorto->id]);

        // --- Estructura de Ingresos ---
        $ingresosSub = Account::create(['name' => 'Ingresos', 'code' => '401', 'type' => 'ingresos', 'parent_id' => $ingresos->id]);
        Account::create(['name' => 'Ingresos por intereses (actividad propia)', 'code' => '401.32', 'type' => 'ingresos', 'parent_id' => $ingresosSub->id]);
        Account::create(['name' => 'Recuperación de cartera castigada', 'code' => '401.38', 'type' => 'ingresos', 'parent_id' => $ingresosSub->id]);

        // --- Estructura de Gastos ---
        $gastosGenerales = Account::create(['name' => 'Gastos generales', 'code' => '601', 'type' => 'gastos', 'parent_id' => $gastos->id]);
        Account::create(['name' => 'Sueldos y salarios', 'code' => '601.01', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
        Account::create(['name' => 'Comisiones a personal', 'code' => '601.03', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
        Account::create(['name' => 'Previsión social', 'code' => '601.09', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
        Account::create(['name' => 'Castigos (gastos no deducibles)', 'code' => '601.10', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
        Account::create(['name' => 'Arrendamiento', 'code' => '601.21', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
        Account::create(['name' => 'Telecomunicaciones', 'code' => '601.25', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);

        $gastosDeVenta = Account::create(['name' => 'Gastos de venta', 'code' => '701', 'type' => 'gastos', 'parent_id' => $gastos->id]);

        $gastosFinancierosSub = Account::create(['name' => 'Gastos financieros', 'code' => '803', 'type' => 'gastos', 'parent_id' => $gastosFinancieros->id]);
        Account::create(['name' => 'Comisiones bancarias', 'code' => '803.01', 'type' => 'gastos', 'parent_id' => $gastosFinancierosSub->id]);

        $this->command->info('El catálogo de cuentas del SAT (versión completa) ha sido cargado.');
    }
}