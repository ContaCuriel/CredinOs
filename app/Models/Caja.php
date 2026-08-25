<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory;

    protected $table = 'cajas';

    protected $fillable = [
        'sucursal_id', 
        'nombre', 
        'estatus', 
        'saldo_actual'
    ];

    public function cortes()
    {
        return $this->hasMany(CorteCaja::class, 'caja_id');
    }

    // Relación exacta y comprobada con el modelo Sucursal
    public function sucursal()
    {
        return $this->belongsTo(\App\Models\Sucursal::class, 'sucursal_id', 'id_sucursal')
                    ->withDefault([
                        'nombre_sucursal' => 'Sucursal Sin Asignar'
                    ]);
    }
}