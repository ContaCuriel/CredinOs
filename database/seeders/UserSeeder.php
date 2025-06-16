<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiamos la tabla antes (¡Cuidado si tienes más usuarios!)
        // DB::table('users')->truncate(); 

        // Buscamos el ID del rol 'Administrador'
        $id_rol_admin = DB::table('roles')->where('nombre_rol', 'Administrador')->value('id_rol');

        DB::table('users')->insert([
            'name' => 'Admin Sistema',
            'email' => 'curiel@facturame.org', 
            'password' => Hash::make('Carcur97#'), // ¡CAMBIA ESTA CONTRASEÑA!
            'id_rol' => $id_rol_admin,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}   