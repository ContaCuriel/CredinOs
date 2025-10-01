<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DebugMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra las rutas de migración que Laravel está usando.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Depuración de Rutas de Migración ---');
        
        $this->line('');
        $this->comment('Conexión de BD por defecto: ' . DB::getDefaultConnection());
        
        $this->line('');
        $this->comment('Rutas conocidas por el servicio "migrator":');
        $migratorPaths = resolve('migrator')->paths();
        if (empty($migratorPaths)) {
            $this->warn('El servicio "migrator" no tiene rutas registradas.');
        } else {
            print_r($migratorPaths);
        }

        $this->line('');
        $this->comment("Valor de 'migrations' en config/database.php para la conexión actual:");
        $connectionName = DB::getDefaultConnection();
        $configPath = config("database.connections.{$connectionName}.migrations");
        if ($configPath) {
            $this->info($configPath);
        } else {
            $this->warn('No hay una ruta de migraciones específica para la conexión actual.');
        }

        return 0;
    }
}