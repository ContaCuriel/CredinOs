<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantDbCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:db-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza una consulta SQL directa a la base de datos del inquilino actual para diagnosticar problemas.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Ejecutando diagnóstico de base de datos para el inquilino actual...");

        try {
            // Usamos la fachada DB para una consulta SQL cruda
            $totalPermissions = DB::table('permissions')->count();
            $renunciaPermission = DB::table('permissions')->where('name', 'like', '%renuncia%')->get();
            $pruebaPermission = DB::table('permissions')->where('name', 'like', '%prueba%')->get();
            $config = config('database.connections.' . config('database.default'));

            $output = [
                'CONTEXTO' => 'CLI / Comando Artisan',
                'Total de permisos contados en la tabla' => $totalPermissions,
                'Permiso "Renuncia" encontrado' => $renunciaPermission,
                'Permiso "Prueba" encontrado' => $pruebaPermission,
                'Configuración de Conexión Activa' => $config
            ];

            // dump() es visualmente más claro en la consola que un simple print
            dump($output);

            $this->info("Diagnóstico completado.");

        } catch (\Exception $e) {
            $this->error("Ocurrió un error al conectar o consultar la base de datos: " . $e->getMessage());
        }

        return 0;
    }
}