<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder implementa el catálogo de cuentas agrupador del SAT (Anexo 24).
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Account::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Nivel 1: Cuentas de Mayor
        $activo = Account::create(['name' => 'Activo', 'code' => '100', 'type' => 'activo']);
        $pasivo = Account::create(['name' => 'Pasivo', 'code' => '200', 'type' => 'pasivo']);
        $capital = Account::create(['name' => 'Capital Contable', 'code' => '300', 'type' => 'capital']);
        $ingresos = Account::create(['name' => 'Ingresos', 'code' => '400', 'type' => 'ingresos']);
        $costos = Account::create(['name' => 'Costos', 'code' => '500', 'type' => 'costos']);
        $gastos = Account::create(['name' => 'Gastos de Operación', 'code' => '600', 'type' => 'gastos']);
        // La cuenta '700' es para 'Gastos de Fabricación', la omitimos si no aplica.
        // La cuenta '800' es para 'Otros Gastos y Resultado Integral de Financiamiento'.
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

        // --- Estructura de Gastos ---
        $gastosGenerales = Account::create(['name' => 'Gastos generales', 'code' => '601', 'type' => 'gastos', 'parent_id' => $gastos->id]);
            Account::create(['name' => 'Sueldos y salarios', 'code' => '601.01', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Comisiones a personal', 'code' => '601.03', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Previsión social', 'code' => '601.09', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Arrendamiento', 'code' => '601.21', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Telecomunicaciones', 'code' => '601.25', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Papelería y útiles de oficina', 'code' => '601.27', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Agua', 'code' => '601.29', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Energía Eléctrica', 'code' => '601.30', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Gas y combustible', 'code' => '601.31', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Fletes y acarreos', 'code' => '601.33', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Mantenimiento y conservación', 'code' => '601.44', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Viáticos y gastos de viaje', 'code' => '601.48', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Limpieza, aseo y sanidad', 'code' => '601.52', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Publicidad y propaganda', 'code' => '601.56', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);
            Account::create(['name' => 'Otros gastos generales', 'code' => '601.84', 'type' => 'gastos', 'parent_id' => $gastosGenerales->id]);

        $gastosDeVenta = Account::create(['name' => 'Gastos de venta', 'code' => '701', 'type' => 'gastos', 'parent_id' => $gastos->id]);
            Account::create(['name' => 'Sueldos y salarios (Ventas)', 'code' => '701.01', 'type' => 'gastos', 'parent_id' => $gastosDeVenta->id]);
            Account::create(['name' => 'Comisiones a personal (Ventas)', 'code' => '701.03', 'type' => 'gastos', 'parent_id' => $gastosDeVenta->id]);
            //... aquí podrías replicar las cuentas de gastos de venta si las manejas por separado

        $gastosFinancierosSub = Account::create(['name' => 'Gastos financieros', 'code' => '803', 'type' => 'gastos', 'parent_id' => $gastosFinancieros->id]);
            Account::create(['name' => 'Comisiones bancarias', 'code' => '803.01', 'type' => 'gastos', 'parent_id' => $gastosFinancierosSub->id]);

        $this->command->info('El catálogo de cuentas del SAT ha sido cargado exitosamente.');
    }
}