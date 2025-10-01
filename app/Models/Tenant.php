<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;
// ¡ESTA ES LA LÍNEA QUE FALTABA!
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection; 

class Tenant extends BaseTenant
{
    // AHORA ESTA LÍNEA FUNCIONARÁ PORQUE LA HEMOS IMPORTADO ARRIBA
    use UsesTenantConnection;

    /**
     * Forzamos a este modelo a usar SIEMPRE la conexión del landlord (central).
     * Esta es la clave para que el sistema pueda encontrar la lista de tenants.
     * @var string
     */
    protected $connection = 'pgsql'; 

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
     * Construye la configuración de la base de datos para este inquilino específico.
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
    
    /**
     * Sobrescribimos este método para que el paquete Spatie sepa que tu columna
     * para el nombre de la base de datos se llama 'db_database'.
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->db_database;
    }
}