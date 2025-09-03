<?php

namespace App\Models;

// SE CAMBIA EL 'use' PARA APUNTAR AL MODELO BASE DEL PAQUETE
use Spatie\Multitenancy\Models\Tenant as BaseTenant;

// LA CLASE AHORA EXTIENDE EL MODELO DEL PAQUETE, LO CUAL INCLUYE TODO LO NECESARIO
class Tenant extends BaseTenant
{
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

    // Ya no necesitamos el método newCollection ni 'implements IsTenant'
    // porque la clase BaseTenant ya se encarga de todo.
}