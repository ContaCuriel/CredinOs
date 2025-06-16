<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ¡Importante!

class SucursalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // DB::table('sucursales')->truncate(); // Limpia la tabla primero

        DB::table('sucursales')->insert([
            ['nombre_sucursal' => 'EJECUTIVA'],
            ['nombre_sucursal' => 'TEXCOCO'],
            ['nombre_sucursal' => 'IXTAPALUCA'],
            ['nombre_sucursal' => 'ZUMPANGO'],
            ['nombre_sucursal' => 'TEXMELUCAN'],
            ['nombre_sucursal' => 'TEXCOCO CREDITICIA'],            
            ['nombre_sucursal' => 'TULYEHUALCO CREDITICIA'],
            ['nombre_sucursal' => 'XICOTEPEC CREDITICIA'],
            ['nombre_sucursal' => 'ZUMPANGO CREDITICIA'],
            ['nombre_sucursal' => 'TEXMELUCAN CREDITICIA'],
            ['nombre_sucursal' => 'AMECAMECA CREDITICIA'],
            ['nombre_sucursal' => 'IXTAPALUCA CREDITICIA'],

        ]);
    }
}