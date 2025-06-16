<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class CreateTenantUser extends Command
{
    /**
     * The name and signature of the console command.
     * Uso: php artisan tenant:user-create --tenant=1
     *
     * @var string
     */
    protected $signature = 'tenant:user-create {--tenant= : The ID of the tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new user for a specific tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantId = $this->option('tenant');
        if (!$tenantId) {
            $this->error('Por favor, especifique el ID del inquilino con --tenant=<id>');
            return 1;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("No se encontró ningún inquilino con el ID {$tenantId}.");
            return 1;
        }

        $this->info("Creando un nuevo usuario para la empresa: {$tenant->name} (Base de datos: {$tenant->database})");

        $name = $this->ask('Nombre del usuario');
        $email = $this->ask('Email del usuario');
        $password = $this->secret('Contraseña para el nuevo usuario');

        try {
            $tenant->execute(function () use ($name, $email, $password) {
                
                $validator = Validator::make(['email' => $email], [
                    'email' => 'unique:tenant.users,email'
                ]);

                if ($validator->fails()) {
                    throw new Exception('El email proporcionado ya existe para este inquilino.');
                }

                User::on('tenant')->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                ]);
            });

            $this->info("¡Éxito! El usuario {$email} ha sido creado para la empresa {$tenant->name}.");

        } catch (Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}