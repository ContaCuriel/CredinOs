<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Comentamos o eliminamos todas las llamadas a otros seeders
        // para enfocarnos únicamente en lo que necesitamos ahora.
        $this->call([
    RolesSeeder::class,
    PuestosSeeder::class,       // Y cualquier otro que tuvieras
    SucursalesSeeder::class,
    UserSeeder::class,
    PermissionSeeder::class,    // Especialmente el que crea los roles y permisos
    AccountSeeder::class,
    CategoriaSeeder::class,
]);
    }
}