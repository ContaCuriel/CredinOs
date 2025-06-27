<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Asegúrate de importar el modelo User
use Spatie\Permission\Models\Role; // Y el modelo Role de Spatie

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos el rol que ya debería haber sido creado por el PermissionSeeder
        // Usamos 'firstOrCreate' por si el PermissionSeeder no se ha corrido, para evitar errores.
        $rolSuperAdmin = Role::firstOrCreate(['name' => 'Super-Admin', 'guard_name' => 'web']);

        // 2. Creamos el usuario administrador principal
        // Usamos 'firstOrCreate' para no crear usuarios duplicados si corres el seeder varias veces
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@tuempresa.com'], // Lo busca por email
            [
                'name' => 'Administrador del Sistema',
                'password' => bcrypt('password'), // ¡Recuerda cambiar 'password' por una contraseña segura!
                'id_sucursal' => 1, // Asigna a la sucursal 1 por defecto, ajústalo si es necesario
            ]
        );

        // 3. Le asignamos el rol de Super-Admin al usuario
        $adminUser->assignRole($rolSuperAdmin);
    }
}