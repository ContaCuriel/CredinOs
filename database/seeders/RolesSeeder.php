<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ¡Importante!

class RolesSeeder extends Seeder // <-- ASEGÚRATE DE TENER ESTA LÍNEA Y LA {
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiamos la tabla antes de insertar para evitar duplicados si se ejecuta varias veces
        // DB::table('roles')->truncate(); 

        // Insertamos los roles
        DB::table('roles')->insert([
            ['nombre_rol' => 'Administrador'],
            ['nombre_rol' => 'Aux. Contable'],
            ['nombre_rol' => 'RH'],
        ]);
    }
} // <-- ASEGÚRATE DE TENER ESTA LLAVE DE CIERRE '}'
