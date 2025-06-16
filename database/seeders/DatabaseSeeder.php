<?php

namespace Database\Seeders;

// Ya no necesitas 'use App\Models\User;' aquí si no usas factories
// use App\Models\User; 

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Borramos o comentamos las llamadas a User::factory() que tenías:
        // \App\Models\User::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Añadimos la llamada a NUESTROS seeders, en un orden lógico:
        $this->call([
            RolesSeeder::class,      // Roles primero
            PuestosSeeder::class,    // <-- AÑADIDO: Puestos después de roles
            SucursalesSeeder::class, // <-- AÑADIDO: Sucursales después de roles
            UserSeeder::class,       // Usuarios al final (depende de Roles)
        ]);
    }
}