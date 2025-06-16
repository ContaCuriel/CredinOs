<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ¡Importante!

class PuestosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('puestos')->truncate(); // Limpia la tabla primero

        DB::table('puestos')->insert([
            ['nombre_puesto' => 'CONTADOR', 'salario_mensual' => 20000.00],
            ['nombre_puesto' => 'AUXILIAR CONTABLE', 'salario_mensual' => 8000.00],
            ['nombre_puesto' => 'ASISTENTE DE TESORERIA', 'salario_mensual' => 12000.00],
            ['nombre_puesto' => 'ASISTENTE DIRECCION', 'salario_mensual' => 20000.00],        
            ['nombre_puesto' => 'MARKETING', 'salario_mensual' => 10000.00],
            ['nombre_puesto' => 'GERENTE REGIONAL', 'salario_mensual' => 25000.00],
            ['nombre_puesto' => 'ADMINISTRADOR A', 'salario_mensual' => 10000.00],
            ['nombre_puesto' => 'ADMINISTRADOR B', 'salario_mensual' => 7500.00],
            ['nombre_puesto' => 'GERENTE A', 'salario_mensual' => 17000.00],
            ['nombre_puesto' => 'GERENTE B', 'salario_mensual' => 15000.00],
            ['nombre_puesto' => 'GERENTE C', 'salario_mensual' => 12500.00],
            ['nombre_puesto' => 'ASESOR V', 'salario_mensual' => 4000.00],
            ['nombre_puesto' => 'ASESOR A', 'salario_mensual' => 8500.00],
        ]);
    }
}