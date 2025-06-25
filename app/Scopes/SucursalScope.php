<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class SucursalScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Solo aplicar el filtro si hay un usuario autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Si el usuario NO tiene el permiso para ver todas las sucursales
            // Y si tiene una sucursal específica asignada
            if (!$user->ver_todas_sucursales && !is_null($user->id_sucursal)) {
                
                // Añadimos automáticamente la condición a la consulta
                $builder->where($model->getTable() . '.id_sucursal', $user->id_sucursal);
            }
        }
    }
}