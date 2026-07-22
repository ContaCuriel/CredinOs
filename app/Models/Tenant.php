<?php

namespace App\Models;

use Spatie\Multitenancy\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant
{
    // NO LLEVA NINGÚN TRAIT ADICIONAL
    // NO LLEVA EL MÉTODO getDatabaseConfig()
    // NO LLEVA LA PROPIEDAD $connection

    protected $fillable = [
        'name',
        'domain',
        'db_database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'configuracion_nomina',
    ];

/**
     * 👈 2. Esto convierte automáticamente el JSON de PostgreSQL en un Array de PHP y viceversa
     */
    protected $casts = [
        'configuracion_nomina' => 'array',
    ];
    /**
     * Sobrescribimos este método para que el paquete Spatie sepa que tu columna
     * para el nombre de la base de datos se llama 'db_database'.
     */
    public function getDatabaseName(): string
    {
        return $this->db_database;
    }
}