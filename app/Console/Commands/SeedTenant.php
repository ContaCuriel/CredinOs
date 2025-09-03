    <?php

    namespace App\Console\Commands;

    use App\Models\Tenant;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\Config;
    use Illuminate\Support\Facades\DB;

    class SeedTenant extends Command
    {
        protected $signature = 'tenant:seed {tenantId} {--class=}';
        protected $description = 'Run a seeder for a specific tenant';

        public function handle()
        {
            $tenantId = $this->argument('tenantId');
            $seederClass = $this->option('class');

            if (!$seederClass) {
                $this->error('The --class option is required.');
                return 1;
            }

            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                $this->error("Tenant with ID {$tenantId} not found.");
                return 1;
            }

            $this->info("Seeding for tenant: {$tenant->name} ({$tenant->domain})");

            // 1. Purgar la conexión anterior para asegurar un estado limpio
            DB::purge('tenant_connection');

            // 2. Configurar la conexión de la base de datos sobre la marcha
            Config::set('database.connections.tenant_connection', [
                'driver'   => 'pgsql',
                'host'     => $tenant->db_host,
                'port'     => $tenant->db_port,
                'database' => $tenant->db_database,
                'username' => $tenant->db_username,
                'password' => $tenant->db_password,
                'charset'  => 'utf8',
                'prefix'   => '',
                'schema'   => 'public',
                'sslmode'  => 'prefer',
            ]);

            // 3. Ejecutar el seeder usando la nueva conexión
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--database' => 'tenant_connection',
                '--force' => true, // Para no pedir confirmación en producción
            ]);

            $this->info("Successfully seeded {$seederClass} for tenant {$tenant->name}.");
            $this->info(Artisan::output());

            return 0;
        }
    }