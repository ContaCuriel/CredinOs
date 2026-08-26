<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // ¡Importante!
use Spatie\Permission\Models\Role; // 🔥 ESTA ES LA LÍNEA QUE FALTABA 🔥

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Define los roles que quieres crear
        $roles = [
            ['name' => 'Super-Admin', 'guard_name' => 'web'], // Asegúrate de que 'Super-Admin' también esté aquí
            ['name' => 'Administrador', 'guard_name' => 'web'],
            ['name' => 'Aux. Contable', 'guard_name' => 'web'],
            ['name' => 'RH', 'guard_name' => 'web'],
            ['name' => 'Admin-RH', 'guard_name' => 'web'], // <-- Agregamos el rol especial para las nuevas empresas
        ];

        foreach ($roles as $roleData) {
            // Usa firstOrCreate para crear el rol solo si no existe
            Role::firstOrCreate($roleData);
        }
    }
}