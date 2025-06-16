<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Multitenancy\Models\Tenant as BaseTenant;
use Spatie\Multitenancy\Contracts\IsTenant;

class Tenant extends BaseTenant implements IsTenant
{
    use HasFactory;

    // Le decimos al modelo que use la conexión 'landlord' para encontrarse a sí mismo.
    protected $connection = 'landlord';

    // Definimos los campos que se pueden llenar masivamente.
    protected $fillable = [
        'name', // ¡Este campo es fundamental para tu estrategia de login!
        'domain',
        'database',
        'email',
        'password',
        'nombre_comercial',
        'logo_path',
        'activo',
    ];

    /**
     * Este es un método crucial. Le dice al paquete cómo configurar la
     * conexión de la base de datos del tenant una vez que se ha encontrado.
     */
    public function getDatabaseConnectionName(): string
    {
        // Devuelve el nombre de la conexión 'tenant' que definimos en config/database.php
        return 'tenant';
    }
}
