<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use Spatie\Multitenancy\Models\Concerns\IsTenant;

class Tenant extends BaseTenant
{
    use UsesTenantConnection; // <-- 2. IMPORTANTE: Usar el Trait

    protected $connection = 'pgsql'; // Esto es correcto, busca tenants en la DB central

    protected $fillable = [
        'name',
        'domain',
        'db_database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
    ];

    protected $hidden = [
        'db_password',
    ];

    /**
     * Este método es llamado por el Trait `UsesTenantConnection`.
     * Le dice al sistema cómo construir la configuración de la base de datos
     * para este inquilino específico, usando las columnas de este modelo.
     *
     * @return array
     */
    public function getDatabaseConfig(): array
    {
        return array_merge(
            config('database.connections.tenant'), // Carga la plantilla de conexión 'tenant'
            [
                'host'     => $this->db_host,
                'database' => $this->db_database,
                'username' => $this->db_username,
                'password' => $this->db_password,
            ]
        );
    }

      public function getDatabaseName(): string
    {
        return $this->db_database;
    }
}