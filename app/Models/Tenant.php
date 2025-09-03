<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantCollection; // <<< AÑADIR ESTA LÍNEA

class Tenant extends Model implements IsTenant
{
    use HasFactory;
    // <<< SE HA ELIMINADO LA LÍNEA 'use UsesTenantModel;' >>>

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

    // <<< SE AÑADE ESTE MÉTODO PARA CUMPLIR CON EL CONTRATO IsTenant >>>
    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }
}

