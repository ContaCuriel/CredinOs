<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // <-- IMPORTANTE PARA EL FILTRO

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';
    protected $primaryKey = 'id_sucursal';

    protected $fillable = [
       'nombre_sucursal',
        'calle',
        'numero',
        'colonia',
        'municipio',
        'estado',
        'status',
    ];

    /**
     * 1. EL FILTRO GLOBAL (LA MAGIA): 
     * Oculta las sucursales inactivas en TODO el sistema por defecto 
     * (Listas de Raya, Excel, menús desplegables, etc).
     */
    protected static function booted()
    {
        static::addGlobalScope('activa', function (Builder $builder) {
            $builder->where('sucursales.status', 'Activa');
        });
    }

    /**
     * 2. LA EXCEPCIÓN: 
     * Permite que el SucursalController original las encuentre por la URL
     * cuando quieras Ver, Editar o Reactivar una sucursal inactiva.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withoutGlobalScope('activa')
                    ->where($field ?? $this->getRouteKeyName(), $value)
                    ->firstOrFail();
    }

    public function empleados()
    {
       return $this->hasMany(Empleado::class, 'id_sucursal', 'id_sucursal');
    }
}