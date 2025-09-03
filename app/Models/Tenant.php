<?php

namespace App\Models;

// SE CAMBIA EL 'use' PARA APUNTAR AL MODELO BASE DEL PAQUETE
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

// LA CLASE AHORA EXTIENDE EL MODELO DEL PAQUETE, LO CUAL INCLUYE TODO LO NECESARIO
class Tenant extends BaseTenant
{
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'pgsql';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'domain',
        'db_database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'db_password',
    ];

    // <<< INICIO DE LA CORRECCIÓN FINAL >>>
    /**
     * Obtiene el nombre de la base de datos para este inquilino.
     * Esto le dice al paquete dónde encontrar la base de datos del inquilino.
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->db_database;
    }
    // <<< FIN DE LA CORRECCIÓN FINAL >>>
}
